<?php
/**
 * FINOVA - Personal Finance Manager
 * کنترلر اعلان‌ها - پاسخ‌های JSON برای زنگوله اعلان در تاپ‌بار
 */

require_once APP_PATH . '/models/Notification.php';

class NotificationController
{
    private Notification $notificationModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->notificationModel = new Notification();
    }

    /**
     * دریافت لیست آخرین اعلان‌ها + تعداد خوانده‌نشده (JSON)
     */
    public function index(): void
    {
        $userId = Auth::id();

        $notifications = $this->notificationModel->allByUser($userId, 10);
        $unreadCount   = $this->notificationModel->unreadCount($userId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * علامت‌گذاری یک اعلان به‌عنوان خوانده‌شده
     */
    public function markRead(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();
        $id     = (int)($_POST['id'] ?? 0);

        $this->notificationModel->markAsRead($id, $userId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true]);
    }

    /**
     * علامت‌گذاری تمام اعلان‌ها به‌عنوان خوانده‌شده
     */
    public function markAllRead(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();

        $this->notificationModel->markAllAsRead($userId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true]);
    }
}
