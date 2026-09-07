<?php require APP_PATH . '/views/partials/header.php'; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><p><?= e($success) ?></p></div>
<?php endif; ?>

<div class="page-header-row">
    <p class="text-muted">برای رسیدن به آرزوهایت پس‌انداز کن 🎯</p>
    <button class="btn btn-primary" onclick="document.getElementById('createGoalModal').classList.add('open')">
        + هدف مالی جدید
    </button>
</div>

<div class="grid-cards">
    <?php foreach ($goals as $g): ?>
        <?php
            $percent = $g['target_amount'] > 0 ? min(100, round(($g['saved_amount'] / $g['target_amount']) * 100)) : 0;
            $isDone  = $percent >= 100;
        ?>
        <div class="card goal-card">
            <div class="budget-card-top">
                <span>🏆 <?= e($g['title']) ?></span>
                <form action="<?= BASE_URL ?>/goals/delete" method="POST"
                      onsubmit="return confirm('آیا از حذف این هدف مطمئن هستید؟');">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                    <button type="submit" class="icon-btn" title="حذف">🗑️</button>
                </form>
            </div>

            <div class="budget-numbers">
                <span class="text-muted">پس‌انداز شده: <?= formatMoney($g['saved_amount']) ?></span>
                <span class="text-muted">هدف: <?= formatMoney($g['target_amount']) ?></span>
            </div>

            <div class="progress-bar">
                <div class="progress-bar-fill <?= $isDone ? 'progress-ok' : 'progress-info' ?>" style="width: <?= $percent ?>%"></div>
            </div>

            <div class="budget-footer">
                <span class="text-muted"><?= $percent ?>٪ تکمیل‌شده</span>
                <?php if ($g['deadline']): ?>
                    <span class="text-muted">مهلت: <?= e($g['deadline']) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($isDone): ?>
                <p class="budget-alert" style="background-color: var(--color-success-light); color: var(--color-success);">
                    🎉 تبریک! به این هدف رسیدی.
                </p>
            <?php else: ?>
                <button class="btn btn-secondary btn-block" style="margin-top:12px;"
                        onclick="openAddFunds(<?= (int)$g['id'] ?>, '<?= e($g['title']) ?>')">
                    + افزودن مبلغ
                </button>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if (empty($goals)): ?>
        <p class="text-muted">هنوز هدف مالی‌ای تعریف نکرده‌اید.</p>
    <?php endif; ?>
</div>

<!-- مودال افزودن هدف جدید -->
<div class="modal-overlay" id="createGoalModal">
    <div class="modal-box card">
        <div class="modal-header">
            <h3>تعریف هدف مالی جدید</h3>
            <button class="icon-btn" onclick="document.getElementById('createGoalModal').classList.remove('open')">✕</button>
        </div>
        <form action="<?= BASE_URL ?>/goals" method="POST">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label for="title">عنوان هدف</label>
                <input type="text" id="title" name="title" placeholder="مثلاً خرید لپ‌تاپ" required>
            </div>
            <div class="form-group">
                <label for="target_amount">مبلغ هدف (تومان)</label>
                <input type="number" step="0.01" id="target_amount" name="target_amount" min="1" required>
            </div>
            <div class="form-group">
                <label for="deadline">مهلت (اختیاری)</label>
                <input type="date" id="deadline" name="deadline">
            </div>
            <button type="submit" class="btn btn-primary btn-block">ذخیره هدف</button>
        </form>
    </div>
</div>

<!-- مودال افزودن مبلغ به هدف -->
<div class="modal-overlay" id="addFundsModal">
    <div class="modal-box card">
        <div class="modal-header">
            <h3 id="addFundsTitle">افزودن مبلغ</h3>
            <button class="icon-btn" onclick="document.getElementById('addFundsModal').classList.remove('open')">✕</button>
        </div>
        <form action="<?= BASE_URL ?>/goals/add-funds" method="POST">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" id="addFundsGoalId">
            <div class="form-group">
                <label for="amount">مبلغ (تومان)</label>
                <input type="number" step="0.01" id="amount" name="amount" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">افزودن به پس‌انداز</button>
        </form>
    </div>
</div>

<script>
    function openAddFunds(id, title) {
        document.getElementById('addFundsGoalId').value = id;
        document.getElementById('addFundsTitle').textContent = 'افزودن مبلغ به «' + title + '»';
        document.getElementById('addFundsModal').classList.add('open');
    }
</script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
