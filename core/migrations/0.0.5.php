<?php

declare(strict_types=1);

use RuEdu\Engine\Migrate;

/**
 * Время уроков в расписании.
 */
return static function (\PDO $pdo, string $prefix): void {
    $schedule = $prefix . 'schedule';

    if (Migrate::tableExists($pdo, $schedule) && !Migrate::columnExists($pdo, $schedule, 'lesson_time')) {
        $pdo->exec("ALTER TABLE `{$schedule}` ADD COLUMN `lesson_time` VARCHAR(50) DEFAULT '' AFTER `lesson_number`");
    }
};
