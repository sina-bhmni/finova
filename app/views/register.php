<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت‌نام | FINOVA</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body class="auth-body">

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-brand">
                <span class="auth-logo">💰</span>
                <h1>FINOVA</h1>
                <p>همین حالا شروع کن به مدیریت هوشمند پول‌ات</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/register" method="POST" class="auth-form" id="registerForm" novalidate>
                <?= Csrf::field() ?>

                <div class="form-group">
                    <label for="name">نام و نام خانوادگی</label>
                    <input type="text" id="name" name="name" placeholder="مثلاً سینا محمدی"
                           value="<?= e($old['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">ایمیل</label>
                    <input type="email" id="email" name="email" placeholder="example@mail.com"
                           value="<?= e($old['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">رمز عبور</label>
                    <input type="password" id="password" name="password" placeholder="حداقل ۶ کاراکتر" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">تکرار رمز عبور</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">ایجاد حساب کاربری</button>
            </form>

            <p class="auth-footer">
                قبلاً ثبت‌نام کرده‌اید؟
                <a href="<?= BASE_URL ?>/login">وارد شوید</a>
            </p>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/validation.js"></script>
</body>
</html>
