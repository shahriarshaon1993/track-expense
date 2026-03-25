<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\BalanceLog;
use App\Models\Budget;
use App\Events\BudgetThresholdReached;
use App\Notifications\BudgetWarningNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreTransactionRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $userId = Auth::id();
        $transactions = Transaction::where('user_id', $userId)->with('category')->latest()->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $categories = auth()->user()->categories;
        $accounts = Account::where('user_id', Auth::id())->get(); // Load user's accounts

        return view('transactions.create', compact('categories', 'accounts'));
    }

    public function store(StoreTransactionRequest $request)
    {
        $user = Auth::user();

        $account = Account::where('id', $request->account_id)->where('user_id', $user->id)->firstOrFail();

        $cleanAmount = str_replace('.', '', $request->amount);
        $formattedAmount = number_format((float) $cleanAmount, 2, '.', '');

        // 🔹 CHECK ACCOUNT BALANCE BEFORE SAVING TRANSACTION
        if ($request->type === 'expense' && $formattedAmount > $account->balance) {
            return redirect()->back()->with('error', 'Account balance is insufficient for this transaction.');
        }

        $transaction = Transaction::create(
            [
                'user_id' => $user->id,
                'amount' => $formattedAmount,
            ] + $request->only(['account_id', 'category_id', 'transaction_date', 'type', 'description']),
        );

        // 🔹 UPDATE ACCOUNT BALANCE
        if ($transaction->type === 'income') {
            $account->increment('balance', $formattedAmount);
        } else {
            $account->decrement('balance', $formattedAmount);
        }

        // 🔹 SAVE TO BALANCE LOGS
        BalanceLog::create([
            'account_id' => $account->id,
            'amount' => $formattedAmount,
            'type' => $transaction->type,
        ]);

        // 🔹 CHECK AND UPDATE BUDGET IF TRANSACTION IS 'EXPENSE'
        if ($transaction->type === 'expense') {
            $month = Carbon::parse($transaction->transaction_date)->format('Y-m');

            $budget = Budget::where('category_id', $transaction->category_id)
                ->where('user_id', $user->id)
                ->where('month', $month)
                ->first();

            if ($budget) {
                $budget->increment('spent', $formattedAmount);

                // 🔹 Calculate budget usage percentage
                $percentUsed = ($budget->spent / $budget->amount) * 100;

                // 🔹 Send warning if usage is between 80% - 99%
                if ($percentUsed >= 80 && $percentUsed < 100) {
                    $user->notify(new BudgetWarningNotification($budget));
                }

                // 🔹 Send ThresholdReached notification if usage is 100% or more
                if ($percentUsed >= 100) {
                    $user->notify(new \App\Notifications\BudgetThresholdReached($budget));
                }
            }
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction added successfully!');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $request->merge(['amount' => str_replace('.', '', $request->amount)]);

        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:categories,id',
            'account_id' => 'required|exists:accounts,id',
        ]);

        $user = Auth::user();
        $account = Account::where('id', $transaction->account_id)->where('user_id', Auth::id())->firstOrFail();

        $cleanAmount = str_replace('.', '', $validated['amount']);
        $formattedAmount = number_format((float) $cleanAmount, 2, '.', '');

        // 🔹 REVERT ACCOUNT BALANCE BEFORE UPDATE
        if ($transaction->type === 'income') {
            $account->decrement('balance', $transaction->amount);
        } else {
            $account->increment('balance', $transaction->amount);
        }

        // 🔹 CHECK BALANCE BEFORE UPDATE
        if ($validated['type'] === 'expense' && $formattedAmount > $account->balance) {
            return redirect()->back()->with('error', 'Account balance is insufficient for this transaction.');
        }

        // 🔹 UPDATE TRANSACTION
        $transaction->update($validated);

        // 🔹 UPDATE ACCOUNT BALANCE
        if ($transaction->type === 'income') {
            $account->increment('balance', $formattedAmount);
        } else {
            $account->decrement('balance', $formattedAmount);
        }

        // 🔹 SAVE TO BALANCE LOGS
        BalanceLog::create([
            'account_id' => $account->id,
            'amount' => $formattedAmount,
            'type' => $transaction->type,
        ]);

        // 🔹 HANDLE BUDGETING (ONLY IF EXPENSE)
        if ($transaction->type === 'expense') {
            $month = Carbon::parse($transaction->transaction_date)->format('Y-m');

            $budget = Budget::where('category_id', $transaction->category_id)
                ->where('user_id', $user->id)
                ->where('month', $month)
                ->first();

            if ($budget) {
                // 🔥 Subtract old amount first, then add the new one
                $spent = Transaction::where('category_id', $transaction->category_id)
                    ->where('user_id', $user->id)
                    ->where('type', 'expense')
                    ->whereRaw("DATE_FORMAT(transaction_date,'%Y-%m') = ?", [$month])
                    ->sum('amount');

                $budget->spent = $spent;
                $budget->save();

                // Optional: Notifications
                $percentUsed = ($budget->spent / $budget->amount) * 100;
                if ($percentUsed >= 80 && $percentUsed < 100) {
                    $user->notify(new BudgetWarningNotification($budget));
                }
                if ($percentUsed >= 100) {
                    $user->notify(new \App\Notifications\BudgetThresholdReached($budget));
                }
            }
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully!');
    }

    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $categories = auth()->user()->categories;
        $accounts = Account::where('user_id', Auth::id())->get(); // Avoid query in blade

        return view('transactions.edit', compact('transaction', 'categories', 'accounts'));
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $account = Account::where('id', $transaction->account_id)->where('user_id', Auth::id())->firstOrFail();

        // 🔹 Revert account balance before deleting transaction
        if ($transaction->type === 'income') {
            $account->decrement('balance', $transaction->amount);
        } else {
            $account->increment('balance', $transaction->amount);
        }

        // 🔹 Subtract spent from budget if transaction is 'expense'
        if ($transaction->type === 'expense') {
            $month = Carbon::parse($transaction->transaction_date)->format('Y-m');

            $budget = Budget::where('user_id', Auth::id())
                ->where('category_id', $transaction->category_id)
                ->where('month', $month)
                ->first();

            if ($budget) {
                // 🔹 Recalculate total spent dynamically
                $spent = Transaction::where('category_id', $transaction->category_id)
                    ->where('user_id', Auth::id())
                    ->where('type', 'expense')
                    ->whereRaw("DATE_FORMAT(transaction_date,'%Y-%m') = ?", [$month])
                    ->where('id', '!=', $transaction->id) // exclude the deleting transaction
                    ->sum('amount');

                $budget->spent = $spent;
                $budget->save();
            }
        }

        // 🔹 SAVE TO BALANCE LOGS
        BalanceLog::create([
            'account_id' => $account->id,
            'amount' => $transaction->amount,
            'type' => 'delete_' . $transaction->type,
        ]);

        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully!');
    }
}
