<?php
/**
 * FINOVA - Personal Finance Manager
 * کنترلر مدیریت دسته‌بندی‌های مالی
 */

require_once APP_PATH . '/models/Category.php';

class CategoryController
{
    private Category $categoryModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->categoryModel = new Category();
    }

    /**
     * نمایش لیست دسته‌بندی‌ها (تفکیک‌شده بر اساس نوع)
     */
    public function index(): void
    {
        $userId = Auth::id();

        $incomeCategories  = $this->categoryModel->allByUser($userId, 'income');
        $expenseCategories = $this->categoryModel->allByUser($userId, 'expense');

        $errors  = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['success']);

        require APP_PATH . '/views/categories.php';
    }

    /**
     * ساخت دسته‌بندی جدید
     */
    public function store(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();

        $validator = new Validator($_POST);
        $validator->required('name', 'نام دسته‌بندی')
                  ->required('type', 'نوع دسته‌بندی')
                  ->in('type', ['income', 'expense'], 'نوع دسته‌بندی');

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            redirect('/categories');
        }

        $icon = trim($_POST['icon'] ?? '') ?: null;

        $this->categoryModel->create($userId, trim($_POST['name']), $_POST['type'], $icon);

        $_SESSION['success'] = 'دسته‌بندی جدید با موفقیت اضافه شد.';
        redirect('/categories');
    }

    /**
     * ویرایش دسته‌بندی موجود
     */
    public function update(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();
        $id     = (int)($_POST['id'] ?? 0);

        $category = $this->categoryModel->findByIdForUser($id, $userId);
        if (!$category) {
            $_SESSION['errors'] = ['general' => 'دسته‌بندی مورد نظر یافت نشد.'];
            redirect('/categories');
        }

        $validator = new Validator($_POST);
        $validator->required('name', 'نام دسته‌بندی')
                  ->required('type', 'نوع دسته‌بندی')
                  ->in('type', ['income', 'expense'], 'نوع دسته‌بندی');

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            redirect('/categories');
        }

        $icon = trim($_POST['icon'] ?? '') ?: null;

        $this->categoryModel->update($id, $userId, trim($_POST['name']), $_POST['type'], $icon);

        $_SESSION['success'] = 'دسته‌بندی با موفقیت ویرایش شد.';
        redirect('/categories');
    }

    /**
     * حذف دسته‌بندی
     */
    public function delete(): void
    {
        Csrf::verifyOrFail();
        $userId = Auth::id();
        $id     = (int)($_POST['id'] ?? 0);

        $category = $this->categoryModel->findByIdForUser($id, $userId);
        if (!$category) {
            $_SESSION['errors'] = ['general' => 'دسته‌بندی مورد نظر یافت نشد.'];
            redirect('/categories');
        }

        $this->categoryModel->delete($id, $userId);

        $_SESSION['success'] = 'دسته‌بندی مورد نظر حذف شد.';
        redirect('/categories');
    }
}
