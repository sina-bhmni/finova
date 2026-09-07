<?php
/**
 * FINOVA - Personal Finance Manager
 * مدل ActivityLog - مسئول ارتباط با جدول activity_logs
 */

class ActivityLog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * ثبت یک رویداد فعالیت جدید برای کاربر
     */
    public function log(int $userId, string $action): void
    {
        $stmt = $this->db->prepare('INSERT INTO activity_logs (user_id, action) VALUES (:user_id, :action)');
        $stmt->execute(['user_id' => $userId, 'action' => $action]);
    }

    /**
     * آخرین فعالیت‌های یک کاربر مشخص
     */
    public function recentByUser(int $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM activity_logs WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * آخرین فعالیت‌های تمام کاربران (برای پنل ادمین)
     */
    public function recentAll(int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, u.name AS user_name
             FROM activity_logs a
             JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
