<?php

declare(strict_types=1);

use RuEdu\Engine\Migrate;

/**
 * Таблица rate_limits.
 */
return static function (\PDO $pdo, string $prefix): void {
    $rateLimits = $prefix . 'rate_limits';

    if (!Migrate::tableExists($pdo, $rateLimits)) {
        $pdo->exec("CREATE TABLE `{$rateLimits}` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `rate_key` VARCHAR(128) NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `hit_at` DATETIME NOT NULL,
            INDEX `idx_key_time` (`rate_key`, `hit_at`),
            INDEX `idx_ip_time` (`ip_address`, `hit_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
};
