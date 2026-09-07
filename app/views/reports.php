<?php require APP_PATH . '/views/partials/header.php'; ?>

<!-- فرم انتخاب بازه زمانی -->
<form method="GET" action="<?= BASE_URL ?>/reports" class="card filter-form">
    <div class="filter-grid" style="grid-template-columns: repeat(auto-fill, minmax(180px,1fr));">
        <div class="form-group">
            <label>از تاریخ</label>
            <input type="date" name="date_from" value="<?= e($dateFrom) ?>">
        </div>
        <div class="form-group">
            <label>تا تاریخ</label>
            <input type="date" name="date_to" value="<?= e($dateTo) ?>">
        </div>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary">اعمال بازه</button>
    </div>
</form>

<!-- خلاصه گزارش -->
<div class="card report-summary">
    <div class="report-row">
        <span>مجموع درآمد</span>
        <strong class="text-success">+<?= formatMoney($report['income']) ?> تومان</strong>
    </div>
    <div class="report-row">
        <span>مجموع هزینه</span>
        <strong class="text-danger">-<?= formatMoney($report['expense']) ?> تومان</strong>
    </div>
    <div class="report-row report-row-total">
        <span>خالص (پس‌انداز دوره)</span>
        <strong style="color: <?= $report['net'] >= 0 ? 'var(--color-success)' : 'var(--color-danger)' ?>">
            <?= formatMoney($report['net']) ?> تومان
        </strong>
    </div>
</div>

<div class="stats-grid">
    <div class="card stat-card">
        <span class="stat-icon">📊</span>
        <div>
            <span class="text-muted stat-label">بیشترین هزینه (دسته‌بندی)</span>
            <?php if ($report['top_category']): ?>
                <h3><?= e($report['top_category']['category_name'] ?? '—') ?></h3>
                <span class="text-muted"><?= formatMoney($report['top_category']['total']) ?> تومان</span>
            <?php else: ?>
                <h3 class="text-muted">—</h3>
            <?php endif; ?>
        </div>
    </div>

    <div class="card stat-card">
        <span class="stat-icon <?= ($report['income_change_percent'] ?? 0) >= 0 ? 'stat-icon-success' : 'stat-icon-danger' ?>">📈</span>
        <div>
            <span class="text-muted stat-label">تغییر درآمد نسبت به دوره قبل</span>
            <?php if ($report['income_change_percent'] !== null): ?>
                <h3 class="<?= $report['income_change_percent'] >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= $report['income_change_percent'] >= 0 ? '+' : '' ?><?= $report['income_change_percent'] ?>٪
                </h3>
            <?php else: ?>
                <h3 class="text-muted">—</h3>
            <?php endif; ?>
        </div>
    </div>

    <div class="card stat-card">
        <span class="stat-icon <?= ($report['expense_change_percent'] ?? 0) <= 0 ? 'stat-icon-success' : 'stat-icon-danger' ?>">📉</span>
        <div>
            <span class="text-muted stat-label">تغییر هزینه نسبت به دوره قبل</span>
            <?php if ($report['expense_change_percent'] !== null): ?>
                <h3 class="<?= $report['expense_change_percent'] <= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= $report['expense_change_percent'] >= 0 ? '+' : '' ?><?= $report['expense_change_percent'] ?>٪
                </h3>
            <?php else: ?>
                <h3 class="text-muted">—</h3>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- جدول تفکیک هزینه بر اساس دسته‌بندی -->
<div class="card table-card">
    <div class="card-header-row" style="padding: 16px 16px 0;">
        <h3 class="chart-title">هزینه‌ها به تفکیک دسته‌بندی</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>دسته‌بندی</th><th>مبلغ</th><th>سهم از کل هزینه</th></tr>
            </thead>
            <tbody>
                <?php foreach ($report['expense_by_category'] as $row): ?>
                    <?php $share = $report['expense'] > 0 ? round(($row['total'] / $report['expense']) * 100) : 0; ?>
                    <tr>
                        <td><?= e($row['category_name'] ?? 'بدون دسته‌بندی') ?></td>
                        <td><?= formatMoney($row['total']) ?> تومان</td>
                        <td>
                            <div class="progress-bar" style="width:120px; display:inline-block; vertical-align:middle;">
                                <div class="progress-bar-fill progress-info" style="width: <?= $share ?>%"></div>
                            </div>
                            <span class="text-muted" style="font-size:11px;"><?= $share ?>٪</span>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($report['expense_by_category'])): ?>
                    <tr><td colspan="3" class="text-muted text-center">هزینه‌ای در این بازه ثبت نشده است.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
