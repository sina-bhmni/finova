<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | FINOVA' : 'FINOVA' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/responsive.css">
</head>
<body class="app-body">

<div class="app-layout">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="auth-logo">💰</span>
            <span>FINOVA</span>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= BASE_URL ?>/dashboard" class="nav-item <?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">
                <span>📊</span> داشبورد
            </a>
            <a href="<?= BASE_URL ?>/transactions" class="nav-item <?= ($activeNav ?? '') === 'transactions' ? 'active' : '' ?>">
                <span>💵</span> تراکنش‌ها
            </a>
            <a href="<?= BASE_URL ?>/wallets" class="nav-item <?= ($activeNav ?? '') === 'wallets' ? 'active' : '' ?>">
                <span>👛</span> کیف پول‌ها
            </a>
            <a href="<?= BASE_URL ?>/categories" class="nav-item <?= ($activeNav ?? '') === 'categories' ? 'active' : '' ?>">
                <span>📂</span> دسته‌بندی‌ها
            </a>
            <a href="<?= BASE_URL ?>/budgets" class="nav-item <?= ($activeNav ?? '') === 'budgets' ? 'active' : '' ?>">
                <span>🎯</span> بودجه‌ها
            </a>
            <a href="<?= BASE_URL ?>/goals" class="nav-item <?= ($activeNav ?? '') === 'goals' ? 'active' : '' ?>">
                <span>🏆</span> اهداف مالی
            </a>
            <a href="<?= BASE_URL ?>/reports" class="nav-item <?= ($activeNav ?? '') === 'reports' ? 'active' : '' ?>">
                <span>📅</span> گزارش‌ها
            </a>
            <a href="<?= BASE_URL ?>/settings" class="nav-item <?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>">
                <span>⚙️</span> تنظیمات
            </a>
            <?php if (Auth::isAdmin()): ?>
            <a href="<?= BASE_URL ?>/admin" class="nav-item <?= ($activeNav ?? '') === 'admin' ? 'active' : '' ?>">
                <span>👨‍💼</span> پنل مدیریت
            </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>/logout" class="nav-item nav-logout">
                <span>🚪</span> خروج
            </a>
        </div>
    </aside>

    <div class="main-area">

        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="باز کردن منو">☰</button>
            <h2 class="page-title"><?= isset($pageTitle) ? e($pageTitle) : '' ?></h2>
            <div class="notification-bell" id="notificationBell">
                <button class="icon-btn bell-btn" id="bellToggle">
                    🔔
                    <span class="bell-badge" id="bellBadge" style="display:none">0</span>
                </button>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-dropdown-header">
                        <span>اعلان‌ها</span>
                        <button id="markAllReadBtn" class="text-muted" style="font-size:11px; background:none; border:none; cursor:pointer;">
                            علامت‌گذاری همه به‌عنوان خوانده‌شده
                        </button>
                    </div>
                    <div id="notificationList" class="notification-list">
                        <p class="text-muted" style="padding:16px; font-size:12px;">در حال بارگذاری...</p>
                    </div>
                </div>
            </div>
            <div class="topbar-user">
                <span><?= e(Auth::user()['name'] ?? '') ?></span>
            </div>
        </header>

        <main class="content-area">
