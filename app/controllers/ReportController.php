<?php
/**
 * FINOVA - Personal Finance Manager
 * کنترلر گزارش مالی
 */

require_once APP_PATH . '/services/ReportService.php';

class ReportController
{
    private ReportService $reportService;

    public function __construct()
    {
        Auth::requireLogin();
        $this->reportService = new ReportService();
    }

    public function index(): void
    {
        $userId = Auth::id();

        // بازه پیش‌فرض: از ابتدای ماه جاری تا امروز
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo   = $_GET['date_to'] ?? date('Y-m-d');

        $report = $this->reportService->generate($userId, $dateFrom, $dateTo);

        $pageTitle = 'گزارش مالی';
        $activeNav = 'reports';

        require APP_PATH . '/views/reports.php';
    }
}
