<?php
/**
 * FINOVA - Personal Finance Manager
 * فایل مسیریابی (Routing) ساده پروژه
 *
 * تمام درخواست‌ها از طریق public/index.php به این فایل هدایت می‌شوند.
 * هر مسیر (route) به یک متد در یک کنترلر متصل است.
 * کنترلرها به‌تدریج در بخش‌های بعدی پروژه اضافه می‌شوند.
 */

require_once __DIR__ . '/../config/config.php';

// گرفتن مسیر درخواستی بدون Query String
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// حذف BASE_URL از ابتدای مسیر تا مسیر خالص باقی بماند
$path = preg_replace('#^' . preg_quote(BASE_URL, '#') . '#', '', $requestUri);
$path = '/' . trim($path, '/');

$method = $_SERVER['REQUEST_METHOD'];

/**
 * جدول مسیرها
 * فرمت: 'METHOD /path' => [ControllerClass, 'method']
 * فایل کنترلر مربوطه در صورت وجود، به‌صورت خودکار require می‌شود.
 */
$routes = [
    // صفحه اصلی -> ریدایرکت به داشبورد یا لاگین
    'GET /' => function () {
        Auth::check() ? redirect('/dashboard') : redirect('/login');
    },

    // احراز هویت (پیاده‌سازی در بخش ۲)
    'GET /login'      => ['AuthController', 'showLogin'],
    'POST /login'     => ['AuthController', 'login'],
    'GET /register'   => ['AuthController', 'showRegister'],
    'POST /register'  => ['AuthController', 'register'],
    'GET /logout'     => ['AuthController', 'logout'],

    // داشبورد (پیاده‌سازی در بخش ۵)
    'GET /dashboard'  => ['DashboardController', 'index'],

    // کیف پول و دسته‌بندی (پیاده‌سازی در بخش ۳)
    'GET /wallets'      => ['WalletController', 'index'],
    'POST /wallets'     => ['WalletController', 'store'],
    'POST /wallets/update' => ['WalletController', 'update'],
    'POST /wallets/delete' => ['WalletController', 'delete'],

    'GET /categories'      => ['CategoryController', 'index'],
    'POST /categories'     => ['CategoryController', 'store'],
    'POST /categories/update' => ['CategoryController', 'update'],
    'POST /categories/delete' => ['CategoryController', 'delete'],

    // تراکنش‌ها (پیاده‌سازی در بخش ۴)
    'GET /transactions'         => ['TransactionController', 'index'],
    'GET /transactions/create'  => ['TransactionController', 'create'],
    'POST /transactions'        => ['TransactionController', 'store'],
    'GET /transactions/edit'    => ['TransactionController', 'edit'],
    'POST /transactions/update' => ['TransactionController', 'update'],
    'POST /transactions/delete' => ['TransactionController', 'delete'],

    // بودجه و اهداف (پیاده‌سازی در بخش ۶)
    'GET /budgets'   => ['BudgetController', 'index'],
    'POST /budgets'  => ['BudgetController', 'store'],
    'POST /budgets/delete' => ['BudgetController', 'delete'],
    'GET /goals'     => ['GoalController', 'index'],
    'POST /goals'    => ['GoalController', 'store'],
    'POST /goals/add-funds' => ['GoalController', 'addFunds'],
    'POST /goals/delete'    => ['GoalController', 'delete'],

    // گزارش‌ها و انتقال (پیاده‌سازی در بخش ۷)
    'GET /reports'   => ['ReportController', 'index'],
    'POST /transfer' => ['TransactionController', 'transfer'],

    // اعلان‌ها (پیاده‌سازی در بخش ۷)
    'GET /notifications'            => ['NotificationController', 'index'],
    'POST /notifications/read'      => ['NotificationController', 'markRead'],
    'POST /notifications/read-all'  => ['NotificationController', 'markAllRead'],

    // تنظیمات و پروفایل و ادمین (پیاده‌سازی در بخش ۸)
    'GET /profile'   => ['SettingController', 'profile'],
    'POST /profile'  => ['SettingController', 'updateProfile'],
    'POST /profile/password' => ['SettingController', 'updatePassword'],
    'GET /settings'  => ['SettingController', 'index'],
    'POST /settings' => ['SettingController', 'update'],
    'GET /admin'     => ['AdminController', 'index'],
];

$key = "$method $path";

if (!array_key_exists($key, $routes)) {
    http_response_code(404);
    echo '404 - صفحه مورد نظر یافت نشد.';
    exit;
}

$handler = $routes[$key];

// اگر مسیر یک تابع Closure باشد (مثل مسیر اصلی /)
if ($handler instanceof Closure) {
    $handler();
    exit;
}

[$controllerName, $methodName] = $handler;
$controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    // این کنترلر هنوز در بخش‌های بعدی پروژه ساخته می‌شود
    http_response_code(503);
    echo "این بخش (<strong>$controllerName</strong>) هنوز پیاده‌سازی نشده است.";
    exit;
}

require_once $controllerFile;

$controller = new $controllerName();
$controller->$methodName();
