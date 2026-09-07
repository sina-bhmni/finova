<?php
/**
 * FINOVA - Personal Finance Manager
 * BudgetService
 * مسئول محاسبه میزان مصرف، باقیمانده و درصد پیشرفت هر بودجه
 * بر اساس تراکنش‌های واقعی هزینه در همان دسته‌بندی و بازه زمانی
 */

require_once APP_PATH . '/models/Budget.php';
require_once APP_PATH . '/models/Transaction.php';

class BudgetService
{
    private Budget $budgetModel;
    private Transaction $transactionModel;

    public function __construct()
    {
        $this->budgetModel = new Budget();
        $this->transactionModel = new Transaction();
    }

    /**
     * دریافت لیست بودجه‌های یک ماه/سال به همراه محاسبه مصرف، باقیمانده، درصد و وضعیت هشدار
     */
    public function getBudgetsWithProgress(int $userId, int $month, int $year): array
    {
        $budgets = $this->budgetModel->allByUserForPeriod($userId, $month, $year);

        $dateFrom = sprintf('%04d-%02d-01', $year, $month);
        $dateTo   = date('Y-m-t', strtotime($dateFrom));

        $result = [];
        foreach ($budgets as $budget) {
            $spent = $this->transactionModel->totalExpenseForCategoryInPeriod(
                $userId,
                (int)$budget['category_id'],
                $dateFrom,
                $dateTo
            );

            $limit     = (float)$budget['amount_limit'];
            $remaining = $limit - $spent;
            $percent   = $limit > 0 ? min(100, round(($spent / $limit) * 100)) : 0;

            $status = 'ok';
            if ($percent >= 100) {
                $status = 'exceeded';
            } elseif ($percent >= 80) {
                $status = 'warning';
            }

            $budget['spent']     = $spent;
            $budget['remaining'] = $remaining;
            $budget['percent']   = $percent;
            $budget['status']    = $status;

            $result[] = $budget;
        }

        return $result;
    }
}
