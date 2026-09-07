<?php
/**
 * FINOVA - Personal Finance Manager
 * مدل Budget - مسئول ارتباط با جدول budgets
 */

class Budget
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * دریافت تمام بودجه‌های یک کاربر برای یک ماه/سال مشخص، به همراه اطلاعات دسته‌بندی
     */
    public function allByUserForPeriod(int $userId, int $month, int $year): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, c.name AS category_name, c.icon AS category_icon
             FROM budgets b
             JOIN categories c ON c.id = b.category_id
             WHERE b.user_id = :user_id AND b.period_month = :month AND b.period_year = :year
             ORDER BY c.name ASC'
        );
        $stmt->execute(['user_id' => $userId, 'month' => $month, 'year' => $year]);
        return $stmt->fetchAll();
    }

    /**
     * پیدا کردن یک بودجه با بررسی مالکیت کاربر
     */
    public function findByIdForUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM budgets WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * بررسی وجود بودجه برای یک دسته‌بندی در یک ماه/سال مشخص (جلوگیری از تکرار)
     */
    public function existsForCategoryPeriod(int $userId, int $categoryId, int $month, int $year): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM budgets
             WHERE user_id = :user_id AND category_id = :category_id
               AND period_month = :month AND period_year = :year
             LIMIT 1'
        );
        $stmt->execute([
            'user_id'     => $userId,
            'category_id' => $categoryId,
            'month'       => $month,
            'year'        => $year,
        ]);
        return (bool)$stmt->fetch();
    }

    /**
     * ساخت بودجه جدید
     */
    public function create(int $userId, int $categoryId, float $amountLimit, int $month, int $year): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO budgets (user_id, category_id, amount_limit, period_month, period_year)
             VALUES (:user_id, :category_id, :amount_limit, :month, :year)'
        );
        $stmt->execute([
            'user_id'      => $userId,
            'category_id'  => $categoryId,
            'amount_limit' => $amountLimit,
            'month'        => $month,
            'year'         => $year,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * ویرایش سقف بودجه
     */
    public function updateLimit(int $id, int $userId, float $amountLimit): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE budgets SET amount_limit = :amount_limit WHERE id = :id AND user_id = :user_id'
        );
        return $stmt->execute(['amount_limit' => $amountLimit, 'id' => $id, 'user_id' => $userId]);
    }

    /**
     * حذف بودجه
     */
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM budgets WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}
