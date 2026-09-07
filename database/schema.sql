-- =========================================================
-- FINOVA - Personal Finance Manager
-- اسکریپت ساخت دیتابیس و جداول
-- =========================================================

CREATE DATABASE IF NOT EXISTS finova_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE finova_db;

-- ---------------------------------------------------------
-- جدول کاربران
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)        NOT NULL,
    email           VARCHAR(150)        NOT NULL UNIQUE,
    password_hash   VARCHAR(255)        NOT NULL,
    profile_picture VARCHAR(255)        NULL,
    currency        VARCHAR(20)         NOT NULL DEFAULT 'Toman',
    role            ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول کیف پول‌ / حساب‌های کاربر
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS wallets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NOT NULL,
    name        VARCHAR(100)    NOT NULL,
    type        ENUM('cash','bank','card','savings','other') NOT NULL DEFAULT 'cash',
    balance     DECIMAL(15,2)   NOT NULL DEFAULT 0,
    currency    VARCHAR(20)     NOT NULL DEFAULT 'Toman',
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wallets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول دسته‌بندی‌های مالی (درآمد / هزینه)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NOT NULL,
    name        VARCHAR(100)    NOT NULL,
    type        ENUM('income','expense') NOT NULL,
    icon        VARCHAR(50)     NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_categories_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول تراکنش‌ها (درآمد / هزینه / انتقال)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id           INT UNSIGNED    NOT NULL,
    wallet_id         INT UNSIGNED    NOT NULL,
    category_id       INT UNSIGNED    NULL,
    type              ENUM('income','expense','transfer') NOT NULL,
    amount            DECIMAL(15,2)   NOT NULL,
    description       VARCHAR(255)    NULL,
    transaction_date  DATE            NOT NULL,
    -- در تراکنش‌های نوع transfer، این ستون حساب مقصد را نگه می‌دارد
    transfer_to_wallet_id INT UNSIGNED NULL,
    created_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transactions_user     FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_transactions_wallet   FOREIGN KEY (wallet_id)   REFERENCES wallets(id)    ON DELETE CASCADE,
    CONSTRAINT fk_transactions_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_transactions_to_wallet FOREIGN KEY (transfer_to_wallet_id) REFERENCES wallets(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول بودجه‌ها
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS budgets (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED    NOT NULL,
    category_id  INT UNSIGNED    NOT NULL,
    amount_limit DECIMAL(15,2)   NOT NULL,
    period_month TINYINT UNSIGNED NOT NULL, -- 1 تا 12
    period_year  SMALLINT UNSIGNED NOT NULL,
    created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_budgets_user     FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_budgets_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_budget_period (user_id, category_id, period_month, period_year)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول اهداف مالی
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS goals (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED    NOT NULL,
    title         VARCHAR(150)    NOT NULL,
    target_amount DECIMAL(15,2)   NOT NULL,
    saved_amount  DECIMAL(15,2)   NOT NULL DEFAULT 0,
    deadline      DATE            NULL,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_goals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول اعلان‌ها
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NOT NULL,
    message     VARCHAR(255)    NOT NULL,
    type        VARCHAR(50)     NOT NULL DEFAULT 'info', -- info, warning, success
    is_read     TINYINT(1)      NOT NULL DEFAULT 0,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول تنظیمات کاربر
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED    NOT NULL UNIQUE,
    language      VARCHAR(10)     NOT NULL DEFAULT 'fa',
    theme         ENUM('light','dark') NOT NULL DEFAULT 'light',
    timezone      VARCHAR(50)     NOT NULL DEFAULT 'Asia/Tehran',
    notifications_enabled TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول لاگ فعالیت‌ها
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NOT NULL,
    action      VARCHAR(255)    NOT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activitylogs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- ایندکس‌های کمکی برای بهبود سرعت جستجو و فیلتر
-- ---------------------------------------------------------
CREATE INDEX idx_transactions_date     ON transactions(transaction_date);
CREATE INDEX idx_transactions_type     ON transactions(type);
CREATE INDEX idx_transactions_user     ON transactions(user_id);
CREATE INDEX idx_categories_user_type  ON categories(user_id, type);
