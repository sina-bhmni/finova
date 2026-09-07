<?php require APP_PATH . '/views/partials/header.php'; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><p><?= e($success) ?></p></div>
<?php endif; ?>

<div class="profile-grid">

    <!-- اطلاعات پروفایل -->
    <div class="card form-card">
        <h3 class="chart-title">اطلاعات حساب کاربری</h3>
        <div class="profile-avatar-row">
            <div class="profile-avatar"><?= mb_substr($user['name'], 0, 1) ?></div>
            <div>
                <strong><?= e($user['name']) ?></strong>
                <p class="text-muted" style="font-size:12px;"><?= e($user['email']) ?></p>
            </div>
        </div>

        <form action="<?= BASE_URL ?>/profile" method="POST">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label for="name">نام و نام خانوادگی</label>
                <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email_display">ایمیل</label>
                <input type="email" id="email_display" value="<?= e($user['email']) ?>" disabled>
            </div>
            <div class="form-group">
                <label for="currency">واحد پول</label>
                <select id="currency" name="currency">
                    <option value="Toman" <?= $user['currency']==='Toman'?'selected':'' ?>>تومان</option>
                    <option value="Rial"  <?= $user['currency']==='Rial'?'selected':'' ?>>ریال</option>
                    <option value="USD"   <?= $user['currency']==='USD'?'selected':'' ?>>دلار آمریکا</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
        </form>
    </div>

    <!-- تغییر رمز عبور -->
    <div class="card form-card">
        <h3 class="chart-title">تغییر رمز عبور</h3>
        <form action="<?= BASE_URL ?>/profile/password" method="POST">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label for="current_password">رمز عبور فعلی</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">رمز عبور جدید</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="new_password_confirmation">تکرار رمز عبور جدید</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
            </div>
            <button type="submit" class="btn btn-primary">تغییر رمز عبور</button>
        </form>
    </div>

</div>

<!-- آخرین فعالیت‌های کاربر -->
<div class="card" style="margin-top:20px;">
    <h3 class="chart-title">آخرین فعالیت‌ها</h3>
    <ul class="activity-list">
        <?php foreach ($recentActivity as $log): ?>
            <li class="activity-item">
                <span>📝 <?= e($log['action']) ?></span>
                <span class="text-muted"><?= e($log['created_at']) ?></span>
            </li>
        <?php endforeach; ?>
        <?php if (empty($recentActivity)): ?>
            <li class="text-muted">فعالیتی ثبت نشده است.</li>
        <?php endif; ?>
    </ul>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
