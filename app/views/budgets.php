<?php require APP_PATH . '/views/partials/header.php'; ?>

<?php
$persianMonths = [
    1=>'فروردین',2=>'اردیبهشت',3=>'خرداد',4=>'تیر',5=>'مرداد',6=>'شهریور',
    7=>'مهر',8=>'آبان',9=>'آذر',10=>'دی',11=>'بهمن',12=>'اسفند',
];
$prevTs = strtotime('-1 month', strtotime("$year-$month-01"));
$nextTs = strtotime('+1 month', strtotime("$year-$month-01"));
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><p><?= e($success) ?></p></div>
<?php endif; ?>

<div class="page-header-row">
    <div class="month-switcher">
        <a href="?month=<?= date('n', $prevTs) ?>&year=<?= date('Y', $prevTs) ?>" class="btn btn-secondary">← ماه قبل</a>
        <strong><?= $persianMonths[$month] ?? $month ?> <?= $year ?></strong>
        <a href="?month=<?= date('n', $nextTs) ?>&year=<?= date('Y', $nextTs) ?>" class="btn btn-secondary">ماه بعد →</a>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('createBudgetModal').classList.add('open')">
        + تعریف بودجه جدید
    </button>
</div>

<div class="grid-cards">
    <?php foreach ($budgets as $b): ?>
        <div class="card budget-card">
            <div class="budget-card-top">
                <span><?= e(($b['category_icon'] ?? '') . ' ' . $b['category_name']) ?></span>
                <form action="<?= BASE_URL ?>/budgets/delete" method="POST"
                      onsubmit="return confirm('آیا از حذف این بودجه مطمئن هستید؟');">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                    <input type="hidden" name="month" value="<?= (int)$month ?>">
                    <input type="hidden" name="year" value="<?= (int)$year ?>">
                    <button type="submit" class="icon-btn" title="حذف">🗑️</button>
                </form>
            </div>

            <div class="budget-numbers">
                <span class="text-muted">مصرف‌شده: <?= formatMoney($b['spent']) ?></span>
                <span class="text-muted">سقف: <?= formatMoney($b['amount_limit']) ?></span>
            </div>

            <div class="progress-bar">
                <div class="progress-bar-fill progress-<?= $b['status'] ?>" style="width: <?= $b['percent'] ?>%"></div>
            </div>

            <div class="budget-footer">
                <span class="text-muted"><?= $b['percent'] ?>٪ استفاده‌شده</span>
                <span class="<?= $b['remaining'] < 0 ? 'text-danger' : 'text-success' ?>">
                    باقیمانده: <?= formatMoney($b['remaining']) ?>
                </span>
            </div>

            <?php if ($b['status'] === 'warning'): ?>
                <p class="budget-alert budget-alert-warning">⚠️ به سقف این بودجه نزدیک شده‌اید.</p>
            <?php elseif ($b['status'] === 'exceeded'): ?>
                <p class="budget-alert budget-alert-danger">🚨 سقف این بودجه رد شده است.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if (empty($budgets)): ?>
        <p class="text-muted">برای این ماه هنوز بودجه‌ای تعریف نشده است.</p>
    <?php endif; ?>
</div>

<!-- مودال افزودن بودجه -->
<div class="modal-overlay" id="createBudgetModal">
    <div class="modal-box card">
        <div class="modal-header">
            <h3>تعریف بودجه جدید</h3>
            <button class="icon-btn" onclick="document.getElementById('createBudgetModal').classList.remove('open')">✕</button>
        </div>
        <form action="<?= BASE_URL ?>/budgets" method="POST">
            <?= Csrf::field() ?>
            <input type="hidden" name="period_month" value="<?= (int)$month ?>">
            <input type="hidden" name="period_year" value="<?= (int)$year ?>">

            <div class="form-group">
                <label for="category_id">دسته‌بندی هزینه</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($expenseCategories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"><?= e(($cat['icon'] ?? '') . ' ' . $cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="amount_limit">سقف بودجه (تومان)</label>
                <input type="number" step="0.01" id="amount_limit" name="amount_limit" min="1" required>
            </div>

            <p class="text-muted" style="font-size:12px; margin-bottom:14px;">
                این بودجه برای <?= $persianMonths[$month] ?? $month ?> <?= $year ?> ثبت می‌شود.
            </p>

            <button type="submit" class="btn btn-primary btn-block">ذخیره بودجه</button>
        </form>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
