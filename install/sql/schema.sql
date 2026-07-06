-- RuEduCMS Database Schema
-- Prefix: {{prefix}}

CREATE TABLE IF NOT EXISTS `{{prefix}}users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `login` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'editor', 'author') NOT NULL DEFAULT 'author',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `last_login` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}pages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(500) NOT NULL UNIQUE,
    `content` LONGTEXT,
    `meta_title` VARCHAR(500) DEFAULT '',
    `meta_description` TEXT,
    `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    `author_id` INT UNSIGNED,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}articles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(500) NOT NULL UNIQUE,
    `content` LONGTEXT,
    `excerpt` TEXT,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `featured_image` VARCHAR(500) DEFAULT '',
    `meta_title` VARCHAR(500) DEFAULT '',
    `meta_description` TEXT,
    `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    `author_id` INT UNSIGNED,
    `published_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`),
    INDEX `idx_published` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}article_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}menus` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `location` VARCHAR(100) NOT NULL DEFAULT 'main',
    `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}menu_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `menu_id` INT UNSIGNED NOT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `url` VARCHAR(500) NOT NULL,
    `target` VARCHAR(20) DEFAULT '_self',
    `sort_order` INT DEFAULT 0,
    INDEX `idx_menu` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}media` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(500) NOT NULL,
    `path` VARCHAR(500) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `size` INT UNSIGNED NOT NULL DEFAULT 0,
    `alt` VARCHAR(500) DEFAULT '',
    `uploaded_by` INT UNSIGNED,
    `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}modules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `settings` JSON,
    `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) NOT NULL UNIQUE,
    `value` TEXT,
    `group` VARCHAR(100) DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}sveden_data` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `section` VARCHAR(100) NOT NULL,
    `data` JSON NOT NULL,
    `updated_at` DATETIME NOT NULL,
    UNIQUE KEY `idx_section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}staff` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `position` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) DEFAULT '',
    `education` TEXT,
    `qualification` VARCHAR(500) DEFAULT '',
    `experience` VARCHAR(255) DEFAULT '',
    `photo` VARCHAR(500) DEFAULT '',
    `email` VARCHAR(255) DEFAULT '',
    `phone` VARCHAR(50) DEFAULT '',
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}schedule_classes` (
    `class_name` VARCHAR(50) NOT NULL PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}schedule` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `class_name` VARCHAR(50) NOT NULL,
    `day_of_week` TINYINT NOT NULL,
    `lesson_number` TINYINT NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `teacher` VARCHAR(255) DEFAULT '',
    `room` VARCHAR(50) DEFAULT '',
    INDEX `idx_class` (`class_name`),
    INDEX `idx_day` (`day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}documents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(500) NOT NULL,
    `category` VARCHAR(255) NOT NULL DEFAULT 'general',
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED DEFAULT 0,
    `published_at` DATE,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}gallery_albums` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT,
    `cover_image` VARCHAR(500) DEFAULT '',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}gallery_images` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `album_id` INT UNSIGNED NOT NULL,
    `path` VARCHAR(500) NOT NULL,
    `title` VARCHAR(255) DEFAULT '',
    `alt` VARCHAR(500) DEFAULT '',
    `sort_order` INT DEFAULT 0,
    INDEX `idx_album` (`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}forms` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `fields` JSON NOT NULL,
    `email_to` VARCHAR(255) DEFAULT '',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}form_submissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `form_id` INT UNSIGNED NOT NULL,
    `data` JSON NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT '',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_form` (`form_id`),
    INDEX `idx_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL,
    UNIQUE KEY `idx_token` (`token`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{prefix}}login_attempts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `attempted_at` DATETIME NOT NULL,
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_time` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Начальные данные модулей
INSERT INTO `{{prefix}}modules` (`name`, `title`, `description`, `enabled`, `created_at`) VALUES
('sveden', 'Сведения об ОО', 'Обязательный раздел сведений об образовательной организации', 1, NOW()),
('news', 'Новости', 'Публикация новостей и объявлений', 1, NOW()),
('staff', 'Педагогический состав', 'Управление информацией о сотрудниках', 1, NOW()),
('schedule', 'Расписание', 'Расписание занятий', 1, NOW()),
('documents', 'Документы', 'Управление документами учреждения', 1, NOW()),
('gallery', 'Фотогалерея', 'Фотоальбомы и галереи', 1, NOW()),
('contacts', 'Контакты', 'Контактная информация и карта', 1, NOW()),
('forms', 'Формы', 'Конструктор форм обратной связи', 1, NOW());

-- Главное меню
INSERT INTO `{{prefix}}menus` (`name`, `location`, `created_at`) VALUES ('Главное меню', 'main', NOW());

INSERT INTO `{{prefix}}menu_items` (`menu_id`, `title`, `url`, `sort_order`) VALUES
(1, 'Главная', '/', 1),
(1, 'Сведения об ОО', '/sveden', 2),
(1, 'Новости', '/news', 3),
(1, 'Педагогический состав', '/staff', 4),
(1, 'Расписание', '/schedule', 5),
(1, 'Документы', '/documents', 6),
(1, 'Галерея', '/gallery', 7),
(1, 'Контакты', '/contacts', 8);

-- Категории новостей
INSERT INTO `{{prefix}}article_categories` (`name`, `slug`, `sort_order`) VALUES
('Новости', 'news', 1),
('Объявления', 'announcements', 2),
('События', 'events', 3);
