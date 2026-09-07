<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="dashboard-greeting">
    <h2>سلام <?= e($user['name']) ?> 👋</h2>
    <p class="text-muted">خلاصه‌ای از وضعیت مالی شما در این ماه</p>
</div>

<!-- کارت‌های آماری -->
<div class="stats-grid">
    <div class="card stat-card">
        <span class="stat-icon">💰</span>
        <div>
            <span class="text-muted stat-label">موجودی کل</span>
            <h3><?= formatMoney($totalBalance) ?> <small>تومان</small></h3>
        </div>
    </div>

    <div class="card stat-card">
        <span class="stat-icon stat-icon-success">📈</span>
        <div>
            <span class="text-muted stat-label">درآمد این ماه</span>
            <h3 class="text-success"><?= formatMoney($monthlyIncome) ?> <small>تومان</small></h3>
        </div>
    </div>

    <div class="card stat-card">
        <span class="stat-icon stat-icon-danger">📉</span>
        <div>
            <span class="text-muted stat-label">هزینه این ماه</span>
            <h3 class="text-danger"><?= formatMoney($monthlyExpense) ?> <small>تومان</small></h3>
        </div>
    </div>

    <div class="card stat-card">
        <span class="stat-icon stat-icon-info">🐷</span>
        <div>
            <span class="text-muted stat-label">پس‌انداز این ماه</span>
            <h3 style="color: <?= $monthlySavings >= 0 ? 'var(--color-success)' : 'var(--color-danger)' ?>">
                <?= formatMoney($monthlySavings) ?> <small>تومان</small>
            </h3>
        </div>
    </div>
</div>

<!-- نمودارها -->
<div class="charts-grid">
    <div class="card chart-card">
        <h3 class="chart-title">روند درآمد و هزینه (۶ ماه اخیر)</h3>
        <canvas id="incomeExpenseChart" height="220"></canvas>
    </div>

    <div class="card chart-card">
        <h3 class="chart-title">هزینه‌ها بر اساس دسته‌بندی (این ماه)</h3>
        <?php if (!empty($expenseByCategory)): ?>
            <canvas id="expenseCategoryChart" height="220"></canvas>
        <?php else: ?>
            <p class="text-muted text-center" style="padding: 40px 0;">هنوز هزینه‌ای در این ماه ثبت نشده است.</p>
        <?php endif; ?>
    </div>
</div>

<!-- آخرین تراکنش‌ها -->
<div class="card">
    <div class="card-header-row">
        <h3 class="chart-title">آخرین تراکنش‌ها</h3>
        <a href="<?= BASE_URL ?>/transactions" class="text-muted" style="font-size:13px;">مشاهده همه ←</a>
    </div>

    <ul class="recent-tx-list">
        <?php foreach ($recentTransactions as $tx): ?>
            <li class="recent-tx-item">
                <span class="recent-tx-icon <?= $tx['type']==='income' ? 'icon-plus' : ($tx['type']==='expense' ? 'icon-minus' : 'icon-transfer') ?>">
                    <?= $tx['type']==='income' ? '+' : ($tx['type']==='expense' ? '-' : '⇄') ?>
                </span>
                <div class="recent-tx-info">
                    <strong><?= e($tx['description'] ?: ($tx['category_name'] ?? 'تراکنش')) ?></strong>
                    <span class="text-muted"><?= e($tx['transaction_date']) ?> · <?= e($tx['wallet_name']) ?></span>
                </div>
                <span class="recent-tx-amount <?= $tx['type']==='income' ? 'text-success' : ($tx['type']==='expense' ? 'text-danger' : '') ?>">
                    <?= $tx['type']==='income' ? '+' : ($tx['type']==='expense' ? '-' : '') ?><?= formatMoney($tx['amount']) ?>
                </span>
            </li>
        <?php endforeach; ?>

        <?php if (empty($recentTransactions)): ?>
            <li class="text-muted">هنوز تراکنشی ثبت نکرده‌اید.</li>
        <?php endif; ?>
    </ul>
</div>

<!-- کتابخانه Chart.js از CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
    const chartLabels        = <?= json_encode($monthlyChartLabels, JSON_UNESCAPED_UNICODE) ?>;
    const chartIncomeData    = <?= json_encode($monthlyIncomeData) ?>;
    const chartExpenseData   = <?= json_encode($monthlyExpenseData) ?>;
    const categoryLabels     = <?= json_encode(array_column($expenseByCategory, 'category_name'), JSON_UNESCAPED_UNICODE) ?>;
    const categoryTotals     = <?= json_encode(array_map('floatval', array_column($expenseByCategory, 'total'))) ?>;
</script>
<script src="<?= BASE_URL ?>/js/charts.js"></script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
