<?php

namespace App\Http\Controllers;

use App\Supports\Number;
use Carbon\Carbon;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $totals = Transaction::where('user_id', $userId)
            ->selectRaw(
                "
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
        ",
            )
            ->first();

        $totalIncome = $totals->total_income ?? 0;
        $totalExpense = $totals->total_expense ?? 0;

        $accounts = Account::where('user_id', $userId)->get();
        $totalAccountsBalance = $accounts->sum('balance');
        $totalBalance = Account::where('user_id', Auth::id())->sum('balance');

        $transactions = Transaction::where('user_id', $userId)->with('category')->latest()->limit(5)->get();

        $balanceHistory = Transaction::selectRaw(
            '
            DATE_FORMAT(transaction_date, "%Y-%m") as month,
            SUM(amount) as balance
        ',
        )
            ->where('user_id', $userId)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // 🔹 Get latest notifications
        $notifications = auth()->user()->unreadNotifications;

        $dailyMessages = [
            "Don't underestimate small expenses — they can add up over time!",
            "Good finances bring peace of mind. You're on the right track!",
            "Keep tracking your expenses — small steps lead to big results!",
            "Hi " . Auth::user()->name . ", have you checked your balance today?",
            "Financial stability starts with simple habits. You're doing great!",
            "A calm wallet is the goal. Keep monitoring your spending!",
            "Every expense you track is a step toward financial freedom. Keep going!",
            "You deserve a life without financial stress. Stay consistent!",
            "Just one click can help organize your financial future better",
            "You and your wallet can work better together — stay organized!"
        ];

        $dailyMessage = $dailyMessages[array_rand($dailyMessages)];

        return view('dashboard', compact('totalIncome', 'totalExpense', 'accounts', 'transactions', 'balanceHistory', 'totalBalance', 'totalAccountsBalance', 'notifications', 'dailyMessage'));
    }

    public function dashboardData()
    {
        $userId = Auth::id();
        $today = Carbon::today();
        $currentMonth = Carbon::now()->format('Y-m');

        // 🔹 Income vs Expense
        $totals = Transaction::where('user_id', $userId)
            ->selectRaw(
                "
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
        ",
            )
            ->first();

        $income = (int) ($totals->total_income ?? 0);
        $expense = (int) ($totals->total_expense ?? 0);

        // 🔹 Monthly balance
        $balanceHistory = Transaction::where('user_id', $userId)
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as date, SUM(amount) as balance')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()->map(fn($item) => [
                'date' => $item->date,
                'balance' => (int) $item->balance
            ]);

        // 🔹 Budget per category
        $budgetUsage = \DB::table('budgets')
            ->join('categories', 'budgets.category_id', '=', 'categories.id')
            ->leftJoin('transactions', function ($join) use ($userId, $currentMonth) {
                $join
                    ->on('budgets.category_id', '=', 'transactions.category_id')
                    ->where('transactions.user_id', '=', $userId)
                    ->whereRaw('DATE_FORMAT(transactions.transaction_date, "%Y-%m") = ?', [$currentMonth]);
            })
            ->where('budgets.user_id', $userId)
            ->groupBy('categories.name', 'budgets.amount')
            ->selectRaw(
                '
            categories.name as category,
            budgets.amount as budget,
            COALESCE(SUM(transactions.amount), 0) as spent
        ',
            )
            ->get();

        // 🔹 Total budget vs. remaining budget this month
        $totalBudget = $budgetUsage->sum('budget');
        $totalSpent = $budgetUsage->sum('spent');
        $remainingBudget = $totalBudget - $totalSpent;

        // 🔹 Average daily expense this month
        $daysPassed = Carbon::now()->day;
        $dailyExpenseAvg = $daysPassed > 0 ? (int) round($totalSpent / $daysPassed) : 0;

        // 🔹 Predicted end-of-month balance (assuming stable income)
        $predictedBalance = Account::where('user_id', $userId)->sum('balance') - $totalSpent;

        // 🔹 Top spending categories
        $topCategories = Transaction::where('transactions.user_id', $userId)->where('transactions.type', 'expense')->join('categories', 'transactions.category_id', '=', 'categories.id')->selectRaw('categories.name, SUM(transactions.amount) as total_spent')->groupBy('categories.name')->orderByDesc('total_spent')->limit(5)->get();

        return response()->json([
            'income' => $income,
            'expense' => $expense,
            'balanceHistory' => $balanceHistory,
            'budgetUsage' => $budgetUsage,
            'totalBudget' => $totalBudget,
            'totalSpent' => $totalSpent,
            'remainingBudget' => $remainingBudget,
            'dailyExpenseAvg' => Number::short($dailyExpenseAvg),
            'predictedBalance' => $predictedBalance,
            'topCategories' => $topCategories,
        ]);
    }
}
