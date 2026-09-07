<?php
/**
 * FINOVA - Personal Finance Manager
 * مدل Notification - مسئول ارتباط با جدول notifications
 */

class Notification
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * دریافت آخرین اعلان‌های کاربر
     */
    public function allByUser(int $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * تعداد اعلان‌های خوانده‌نشده
     */
    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = :user_id AND is_read = 0'
        );
        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetch()['cnt'];
    }

    /**
     * ساخت اعلان جدید
     */
    public function create(int $userId, string $message, string $type = 'info'): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, message, type, is_read) VALUES (:user_id, :message, :type, 0)'
        );
        $stmt->execute(['user_id' => $userId, 'message' => $message, 'type' => $type]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * بررسی وجود اعلانی با همان متن (برای جلوگیری از تکرار اعلان‌های هشدار بودجه)
     */
    public function existsWithMessage(int $userId, string $message): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM notifications WHERE user_id = :user_id AND message = :message LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId, 'message' => $message]);
        return (bool)$stmt->fetch();
    }

    /**
     * علامت‌گذاری یک اعلان به‌عنوان خوانده‌شده
     */
    public function markAsRead(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id'
        );
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    /**
     * علامت‌گذاری تمام اعلان‌های کاربر به‌عنوان خوانده‌شده
     */
    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id');
        return $stmt->execute(['user_id' => $userId]);
    }
}
