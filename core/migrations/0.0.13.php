<?php

declare(strict_types=1);

use RuEdu\Engine\Migrate;

/**
 * Field Groups: схемы полей и field_data для страниц.
 */
return static function (\PDO $pdo, string $prefix): void {
    $groups = $prefix . 'field_groups';
    $fields = $prefix . 'fields';
    $pages = $prefix . 'pages';

    if (!Migrate::tableExists($pdo, $groups)) {
        $pdo->exec("CREATE TABLE `{$groups}` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `locations` JSON NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    if (!Migrate::tableExists($pdo, $fields)) {
        $pdo->exec("CREATE TABLE `{$fields}` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `group_id` INT UNSIGNED NOT NULL,
            `parent_id` INT UNSIGNED NULL,
            `name` VARCHAR(100) NOT NULL,
            `label` VARCHAR(255) NOT NULL DEFAULT '',
            `type` VARCHAR(50) NOT NULL DEFAULT 'text',
            `config` JSON NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            INDEX `idx_group` (`group_id`),
            INDEX `idx_parent` (`parent_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    if (Migrate::tableExists($pdo, $pages) && !Migrate::columnExists($pdo, $pages, 'field_data')) {
        $pdo->exec("ALTER TABLE `{$pages}` ADD COLUMN `field_data` JSON NULL AFTER `content_blocks`");
    }

    if (Migrate::tableExists($pdo, $pages) && Migrate::columnExists($pdo, $pages, 'content_mode')) {
        $pdo->exec("ALTER TABLE `{$pages}` MODIFY COLUMN `content_mode` ENUM('html','blocks','fields') NOT NULL DEFAULT 'html'");
    }
};
