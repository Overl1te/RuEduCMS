<?php

declare(strict_types=1);

use RuEdu\Engine\Migrate;

/**
 * Мастер первоначальной настройки: существующие сайты считаются уже настроенными.
 */
return static function (\PDO $pdo, string $prefix): void {
    $table = $prefix . 'settings';
    if (!Migrate::tableExists($pdo, $table)) {
        return;
    }

    $check = $pdo->prepare("SELECT id FROM `{$table}` WHERE `key` = 'setup_completed' LIMIT 1");
    $check->execute();
    if ($check->fetchColumn()) {
        return;
    }

    $insert = $pdo->prepare(
        "INSERT INTO `{$table}` (`key`, `value`, `group`) VALUES ('setup_completed', '1', 'general')"
    );
    $insert->execute();
};
