<?php
/**
 * FINOVA - Personal Finance Manager
 * کنترلر داشبورد اصلی
 */

require_once APP_PATH . '/models/Wallet.php';
require_once APP_PATH . '/models/Transaction.php';

class DashboardController
{
    private Wallet $walletModel;
    private Transaction $transactionModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->walletModel = new Wallet();
        $this->transactionModel = new Transaction();
    }

    public function index(): void
    {
        $userId = Auth::id();
        $user   = Auth::user();

        // بازه ماه جاری
        $monthStart = date('Y-m-01');
        $monthEnd   = date('Y-m-t');

        $totalBalance = $this->walletModel->totalBalance($userId);
        $monthTotals  = $this->transactionModel->totalsByTypeForPeriod($userId, $monthStart, $monthEnd);

        $monthlyIncome  = $monthTotals['income'];
        $monthlyExpense = $monthTotals['expense'];
        $monthlySavings = $monthlyIncome - $monthlyExpense;

        // داده نمودار درآمد/هزینه ۶ ماه اخیر
        $monthlyChartLabels  = [];
        $monthlyIncomeData   = [];
        $monthlyExpenseData  = [];

        $persianMonths = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ];

        for ($i = 5; $i >= 0; $i--) {
            $ts = strtotime("-$i months", strtotime(date('Y-m-01')));
            $start = date('Y-m-01', $ts);
            $end   = date('Y-m-t', $ts);

            $totals = $this->transactionModel->totalsByTypeForPeriod($userId, $start, $end);

            // برچسب ساده میلادی؛ در صورت نیاز به تقویم شمسی واقعی باید از کتابخانه جداگانه استفاده شود
            $monthlyChartLabels[] = date('M Y', $ts);
            $monthlyIncomeData[]  = $totals['income'];
            $monthlyExpenseData[] = $totals['expense'];
        }

        // نمودار هزینه بر اساس دسته‌بندی (ماه جاری)
        $expenseByCategory = $this->transactionModel->expenseByCategoryForPeriod($userId, $monthStart, $monthEnd);

        $recentTransactions = $this->transactionModel->recentByUser($userId, 6);

        $pageTitle = 'داشبورد';
        $activeNav = 'dashboard';

        require APP_PATH . '/views/dashboard.php';
    }
}
