<?php
/**
 * FINOVA - Personal Finance Manager
 * کنترلر پنل مدیریت (Admin)
 */

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Transaction.php';
require_once APP_PATH . '/models/ActivityLog.php';

class AdminController
{
    private User $userModel;
    private Transaction $transactionModel;
    private ActivityLog $activityLogModel;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->userModel        = new User();
        $this->transactionModel = new Transaction();
        $this->activityLogModel = new ActivityLog();
    }

    public function index(): void
    {
        $totalUsers        = $this->userModel->countAll();
        $activeUsers       = $this->userModel->countActiveThisMonth();
        $totalTransactions = $this->transactionModel->countAllSystemWide();

        $users          = $this->userModel->allForAdmin();
        $recentActivity = $this->activityLogModel->recentAll(15);

        $pageTitle = 'پنل مدیریت';
        $activeNav = 'admin';

        require APP_PATH . '/views/admin.php';
    }
}
