<?php

namespace App\Http\Controllers;

use App\Supports\Number;
use Carbon\Carbon;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $totals = $this->getTotals($userId);

        $totalIncome = $totals['total_income'];
        $totalExpense = $totals['total_expense'];

        $savingRate = $this->getSavingRate($totalIncome, $totalExpense);

        $accounts = Account::where('user_id', $userId)->get();
        $totalAccountsBalance = $accounts->sum('balance');
        $totalBalance = Account::where('user_id', Auth::id())->sum('balance');

        $transactions = Transaction::where('user_id', $userId)->with('category')->latest()->limit(5)->get();

        $balanceHistory = $this->getBalanceHistory($userId);

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

        $months = Transaction::where('user_id', Auth::id())
            ->selectRaw("DISTINCT DATE_FORMAT(transaction_date,'%Y-%m') as month")
            ->orderBy('month', 'desc')
            ->pluck('month');

        $currentMonth = Carbon::now()->format('Y-m');

        $previousMonth = Carbon::parse($currentMonth)->subMonth()->format('Y-m');

        $previousTotals = $this->getTotals($userId, $previousMonth);

        $incomeGrowth = $this->calculateGrowth($totalIncome, $previousTotals['total_income']);
        $expenseGrowth = $this->calculateGrowth($totalExpense, $previousTotals['total_expense']);

        return view('dashboard', [
            'months' => $months,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'savingRate' => $savingRate,
            'accounts' => $accounts,
            'transactions' => $transactions,
            'balanceHistory' => $balanceHistory,
            'totalBalance' => $totalBalance,
            'totalAccountsBalance' => $totalAccountsBalance,
            'incomeGrowth' => $incomeGrowth,
            'expenseGrowth' => $expenseGrowth,
            'notifications' => $notifications,
            'dailyMessage' => $dailyMessage,
        ]);
    }

//    public function dashboardData()
//    {
//        $userId = Auth::id();
//        $today = Carbon::today();
//        $currentMonth = Carbon::now()->format('Y-m');
//
//        // 🔹 Income vs Expense
//        $totals = Transaction::where('user_id', $userId)
//            ->selectRaw(
//                "
//            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
//            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
//        ",
//            )
//            ->first();
//
//        $income = (int) ($totals->total_income ?? 0);
//        $expense = (int) ($totals->total_expense ?? 0);
//
//        // 🔹 Monthly balance
//        $balanceHistory = Transaction::where('user_id', $userId)
//            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as date, SUM(amount) as balance')
//            ->groupBy('date')
//            ->orderBy('date', 'asc')
//            ->get()->map(fn($item) => [
//                'date' => $item->date,
//                'balance' => (int) $item->balance
//            ]);
//
//        // 🔹 Budget per category
//        $budgetUsage = \DB::table('budgets')
//            ->join('categories', 'budgets.category_id', '=', 'categories.id')
//            ->leftJoin('transactions', function ($join) use ($userId, $currentMonth) {
//                $join
//                    ->on('budgets.category_id', '=', 'transactions.category_id')
//                    ->where('transactions.user_id', '=', $userId)
//                    ->whereRaw('DATE_FORMAT(transactions.transaction_date, "%Y-%m") = ?', [$currentMonth]);
//            })
//            ->where('budgets.user_id', $userId)
//            ->groupBy('categories.name', 'budgets.amount')
//            ->selectRaw(
//                '
//            categories.name as category,
//            budgets.amount as budget,
//            COALESCE(SUM(transactions.amount), 0) as spent
//        ',
//            )
//            ->get();
//
//        // 🔹 Total budget vs. remaining budget this month
//        $totalBudget = $budgetUsage->sum('budget');
//        $totalSpent = $budgetUsage->sum('spent');
//        $remainingBudget = $totalBudget - $totalSpent;
//
//        // 🔹 Average daily expense this month
//        $daysPassed = Carbon::now()->day;
//        $dailyExpenseAvg = $daysPassed > 0 ? (int) round($totalSpent / $daysPassed) : 0;
//
//        // 🔹 Predicted end-of-month balance (assuming stable income)
//        $predictedBalance = Account::where('user_id', $userId)->sum('balance') - $totalSpent;
//
//        // 🔹 Top spending categories
//        $topCategories = Transaction::where('transactions.user_id', $userId)->where('transactions.type', 'expense')->join('categories', 'transactions.category_id', '=', 'categories.id')->selectRaw('categories.name, SUM(transactions.amount) as total_spent')->groupBy('categories.name')->orderByDesc('total_spent')->limit(5)->get();
//
//        return response()->json([
//            'income' => $income,
//            'expense' => $expense,
//            'balanceHistory' => $balanceHistory,
//            'budgetUsage' => $budgetUsage,
//            'totalBudget' => $totalBudget,
//            'totalSpent' => $totalSpent,
//            'remainingBudget' => $remainingBudget,
//            'dailyExpenseAvg' => Number::short($dailyExpenseAvg),
//            'predictedBalance' => $predictedBalance,
//            'topCategories' => $topCategories,
//        ]);
//    }

    public function dashboardData(Request $request)
    {
        $authId = Auth::id();
        $month = $request->month;

        $incomeGrowth = 0;
        $expenseGrowth = 0;

        // 🔹 Total Income vs Expense (All Time)
        $totals = $this->getTotals($authId, $month);

        $totalIncome = $totals['total_income'];
        $totalExpense = $totals['total_expense'];

        if ($month) {
            $previousMonth = Carbon::parse($month)->subMonth()->format('Y-m');

            $previousTotals = $this->getTotals($authId, $previousMonth);

            $incomeGrowth = $this->calculateGrowth($totalIncome, $previousTotals['total_income']);
            $expenseGrowth = $this->calculateGrowth($totalExpense, $previousTotals['total_expense']);
        }

        $savingRate = $this->getSavingRate($totalIncome, $totalExpense);

        // 🔹 Balance History (All Time OR Monthly)
        $balanceHistory = $this->getBalanceHistory($authId);

        // 🔹 Budget per category
        $budgetUsage = $this->getBudgetUsage($authId, $month);

        // 🔹 Budget Summary
        $totalBudget = $budgetUsage->sum('budget');
        $totalSpent = $budgetUsage->sum('spent');
        $remainingBudget = $totalBudget - $totalSpent;

        // 🔹 Average Daily Expense
        if ($month) {
            $currentDay = Carbon::now()->day;
            $dailyExpenseAvg = $currentDay > 0 ? (int)round($totalSpent / $currentDay) : 0;
        } else {
            $dailyExpenseAvg = 0;
        }

        // 🔹 Predicted Balance
        $predictedBalance = Account::where('user_id', $authId)->sum('balance') - $totalSpent;

        // 🔹 Top Categories
        $topCategories = $this->getTopCategories($authId, $month);

        return response()->json([
            'income' => $totalIncome,
            'expense' => $totalExpense,
            'totalIncome' => Number::short($totalIncome),
            'totalExpense' => Number::short($totalExpense),
            'savingRate' => $savingRate,
            'balanceHistory' => $balanceHistory,
            'budgetUsage' => $budgetUsage,
            'totalBudget' => $totalBudget,
            'totalSpent' => $totalSpent,
            'remainingBudget' => $remainingBudget,
            'dailyExpenseAvg' => Number::short($dailyExpenseAvg),
            'predictedBalance' => $predictedBalance,
            'incomeGrowth' => $incomeGrowth,
            'expenseGrowth' => $expenseGrowth,
            'topCategories' => $topCategories,
        ]);
    }

    public function getTotals(int $authId, ?string $month = null): array
    {
        $totals = Transaction::where('user_id', $authId)
            ->when($month, function ($query) use ($month) {
                $query->whereBetween('transaction_date', [
                    Carbon::parse($month)->startOfMonth(),
                    Carbon::parse($month)->endOfMonth()
                ]);
            })
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
            ")->first();

        return [
            'total_income' => (int) ($totals->total_income ?? 0),
            'total_expense' => (int)($totals->total_expense ?? 0),
        ];
    }

    public function getSavingRate(int $income, int $expense): int
    {
        $netBalance = $income - $expense;

        return $income > 0 ? round(($netBalance / $income) * 100, 2) : 0;
    }

    public function getBalanceHistory(int $authId): Collection
    {
        return Transaction::where('user_id', $authId)
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as date, SUM(amount) as balance')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()->map(fn($item) => [
                'date' => $item->date,
                'balance' => (int) $item->balance
            ]);
    }

    public function getBudgetUsage(int $authId, ?string $month = null)
    {
        return \DB::table('budgets')
            ->join('categories', 'budgets.category_id', '=', 'categories.id')
            ->leftJoin('transactions', function ($join) use ($authId, $month) {
                $join->on('budgets.category_id', '=', 'transactions.category_id')
                    ->where('transactions.user_id', '=', $authId);

                if ($month) {
                    $join->whereRaw('DATE_FORMAT(transactions.transaction_date, "%Y-%m") = ?', [$month]);
                }
            })
            ->where('budgets.user_id', $authId)
            ->groupBy('categories.name', 'budgets.amount')
            ->selectRaw('
                categories.name as category,
                budgets.amount as budget,
                COALESCE(SUM(transactions.amount), 0) as spent
            ')->get();
    }

    public function getTopCategories(int $authId, ?string $month = null): Collection
    {
        return Transaction::where('transactions.user_id', $authId)
            ->where('transactions.type', 'expense')
            ->when($month, function ($query) use ($month) {
                $query->whereRaw('DATE_FORMAT(transactions.transaction_date, "%Y-%m") = ?', [$month]);
            })
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, SUM(transactions.amount) as total_spent')
            ->groupBy('categories.name')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();
    }

    public function calculateGrowth(int $current, int $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
