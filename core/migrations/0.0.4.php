<?php

declare(strict_types=1);

use RuEdu\Engine\Migrate;

/**
 * Реестр классов для модуля расписания.
 */
return static function (\PDO $pdo, string $prefix): void {
    $classes = $prefix . 'schedule_classes';
    $schedule = $prefix . 'schedule';

    if (!Migrate::tableExists($pdo, $classes)) {
        $pdo->exec("CREATE TABLE `{$classes}` (
            `class_name` VARCHAR(50) NOT NULL PRIMARY KEY
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        if (Migrate::tableExists($pdo, $schedule)) {
            $pdo->exec("INSERT IGNORE INTO `{$classes}` (`class_name`)
                SELECT DISTINCT `class_name` FROM `{$schedule}`");
        }
    }
};
