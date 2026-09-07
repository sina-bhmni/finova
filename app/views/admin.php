<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="stats-grid">
    <div class="card stat-card">
        <span class="stat-icon">👥</span>
        <div>
            <span class="text-muted stat-label">کل کاربران</span>
            <h3><?= (int)$totalUsers ?></h3>
        </div>
    </div>
    <div class="card stat-card">
        <span class="stat-icon stat-icon-success">🟢</span>
        <div>
            <span class="text-muted stat-label">کاربران فعال این ماه</span>
            <h3><?= (int)$activeUsers ?></h3>
        </div>
    </div>
    <div class="card stat-card">
        <span class="stat-icon stat-icon-info">💳</span>
        <div>
            <span class="text-muted stat-label">کل تراکنش‌های ثبت‌شده</span>
            <h3><?= (int)$totalTransactions ?></h3>
        </div>
    </div>
</div>

<div class="admin-columns">

    <!-- لیست کاربران -->
    <div class="card table-card">
        <div class="card-header-row" style="padding:16px 16px 0;">
            <h3 class="chart-title">لیست کاربران</h3>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>نام</th><th>ایمیل</th><th>نقش</th><th>واحد پول</th><th>تاریخ عضویت</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= e($u['name']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><span class="badge <?= $u['role']==='admin'?'badge-info':'badge-success' ?>">
                                <?= $u['role']==='admin' ? 'ادمین' : 'کاربر' ?>
                            </span></td>
                            <td><?= e($u['currency']) ?></td>
                            <td><?= e(substr($u['created_at'], 0, 10)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- آخرین فعالیت‌های سیستم -->
    <div class="card">
        <h3 class="chart-title">آخرین فعالیت‌های سیستم</h3>
        <ul class="activity-list">
            <?php foreach ($recentActivity as $log): ?>
                <li class="activity-item">
                    <span>👤 <strong><?= e($log['user_name']) ?></strong> — <?= e($log['action']) ?></span>
                    <span class="text-muted"><?= e($log['created_at']) ?></span>
                </li>
            <?php endforeach; ?>
            <?php if (empty($recentActivity)): ?>
                <li class="text-muted">فعالیتی ثبت نشده است.</li>
            <?php endif; ?>
        </ul>
    </div>

</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
