<?php
/**
 * FINOVA - Personal Finance Manager
 * مدل Setting - مسئول ارتباط با جدول settings
 */

class Setting
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * دریافت تنظیمات کاربر؛ در صورت نبود رکورد، یک رکورد پیش‌فرض (تم روشن) ساخته می‌شود
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM settings WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            $this->db->prepare(
                'INSERT INTO settings (user_id, language, theme, timezone, notifications_enabled)
                 VALUES (:user_id, "fa", "light", "Asia/Tehran", 1)'
            )->execute(['user_id' => $userId]);

            $stmt->execute(['user_id' => $userId]);
            $row = $stmt->fetch();
        }

        return $row;
    }

    /**
     * به‌روزرسانی تنظیمات کاربر
     */
    public function update(int $userId, string $language, string $theme, string $timezone, bool $notificationsEnabled): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE settings SET language = :language, theme = :theme, timezone = :timezone,
                notifications_enabled = :notifications_enabled
             WHERE user_id = :user_id'
        );
        return $stmt->execute([
            'language'               => $language,
            'theme'                  => $theme,
            'timezone'               => $timezone,
            'notifications_enabled'  => $notificationsEnabled ? 1 : 0,
            'user_id'                => $userId,
        ]);
    }
}
