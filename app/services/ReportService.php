<?php
/**
 * FINOVA - Personal Finance Manager
 * ReportService
 * تولید گزارش مالی برای یک بازه زمانی دلخواه، به همراه مقایسه با دوره مشابه قبلی
 */

require_once APP_PATH . '/models/Transaction.php';

class ReportService
{
    private Transaction $transactionModel;

    public function __construct()
    {
        $this->transactionModel = new Transaction();
    }

    /**
     * تولید گزارش کامل برای بازه [dateFrom, dateTo]
     */
    public function generate(int $userId, string $dateFrom, string $dateTo): array
    {
        $totals = $this->transactionModel->totalsByTypeForPeriod($userId, $dateFrom, $dateTo);
        $income  = $totals['income'];
        $expense = $totals['expense'];
        $net     = $income - $expense;

        $expenseByCategory = $this->transactionModel->expenseByCategoryForPeriod($userId, $dateFrom, $dateTo);

        // محاسبه بازه مشابه قبلی (به همان طول روز) برای مقایسه
        $days = (strtotime($dateTo) - strtotime($dateFrom)) / 86400 + 1;
        $prevDateTo   = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
        $prevDateFrom = date('Y-m-d', strtotime($prevDateTo . ' -' . ($days - 1) . ' days'));

        $prevTotals = $this->transactionModel->totalsByTypeForPeriod($userId, $prevDateFrom, $prevDateTo);

        $expenseChangePercent = $prevTotals['expense'] > 0
            ? round((($expense - $prevTotals['expense']) / $prevTotals['expense']) * 100, 1)
            : null;

        $incomeChangePercent = $prevTotals['income'] > 0
            ? round((($income - $prevTotals['income']) / $prevTotals['income']) * 100, 1)
            : null;

        // بیشترین هزینه (بزرگترین دسته‌بندی) و بیشترین تراکنش منفرد
        $topCategory = $expenseByCategory[0] ?? null;

        return [
            'income'                => $income,
            'expense'               => $expense,
            'net'                   => $net,
            'expense_by_category'   => $expenseByCategory,
            'top_category'          => $topCategory,
            'previous_period'       => [
                'date_from' => $prevDateFrom,
                'date_to'   => $prevDateTo,
                'income'    => $prevTotals['income'],
                'expense'   => $prevTotals['expense'],
            ],
            'income_change_percent'  => $incomeChangePercent,
            'expense_change_percent' => $expenseChangePercent,
        ];
    }
}
