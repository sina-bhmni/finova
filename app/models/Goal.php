<?php
/**
 * FINOVA - Personal Finance Manager
 * مدل Goal - مسئول ارتباط با جدول goals
 */

class Goal
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * دریافت تمام اهداف مالی یک کاربر
     */
    public function allByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM goals WHERE user_id = :user_id ORDER BY (deadline IS NULL), deadline ASC, created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * پیدا کردن یک هدف با بررسی مالکیت کاربر
     */
    public function findByIdForUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM goals WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * ساخت هدف مالی جدید
     */
    public function create(int $userId, string $title, float $targetAmount, ?string $deadline): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO goals (user_id, title, target_amount, saved_amount, deadline)
             VALUES (:user_id, :title, :target_amount, 0, :deadline)'
        );
        $stmt->execute([
            'user_id'       => $userId,
            'title'         => $title,
            'target_amount' => $targetAmount,
            'deadline'      => $deadline,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * ویرایش اطلاعات هدف (عنوان، مبلغ هدف، مهلت)
     */
    public function update(int $id, int $userId, string $title, float $targetAmount, ?string $deadline): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE goals SET title = :title, target_amount = :target_amount, deadline = :deadline
             WHERE id = :id AND user_id = :user_id'
        );
        return $stmt->execute([
            'title'         => $title,
            'target_amount' => $targetAmount,
            'deadline'      => $deadline,
            'id'            => $id,
            'user_id'       => $userId,
        ]);
    }

    /**
     * افزودن مبلغ به پس‌انداز هدف
     */
    public function addFunds(int $id, int $userId, float $amount): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE goals SET saved_amount = saved_amount + :amount WHERE id = :id AND user_id = :user_id'
        );
        return $stmt->execute(['amount' => $amount, 'id' => $id, 'user_id' => $userId]);
    }

    /**
     * حذف هدف مالی
     */
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM goals WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}
