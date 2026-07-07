<?php

declare(strict_types=1);

use RuEdu\Engine\Migrate;

/**
 * Удаление legacy-колонок конструктора с таблицы pages.
 */
return static function (\PDO $pdo, string $prefix): void {
    $pages = $prefix . 'pages';
    $settings = $prefix . 'settings';

    if (Migrate::tableExists($pdo, $pages)) {
        foreach (['content_blocks', 'field_data', 'content_mode'] as $column) {
            if (Migrate::columnExists($pdo, $pages, $column)) {
                $pdo->exec("ALTER TABLE `{$pages}` DROP COLUMN `{$column}`");
            }
        }
    }

    if (Migrate::tableExists($pdo, $settings)) {
        $stmt = $pdo->prepare("INSERT INTO `{$settings}` (`key`, `value`, `group`) VALUES ('schema_builder_removed', '1', 'system')
            ON DUPLICATE KEY UPDATE `value` = '1'");
        $stmt->execute();
    }
};
