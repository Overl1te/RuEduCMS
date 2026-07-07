<?php

declare(strict_types=1);

use RuEdu\Engine\Migrate;

/**
 * Удаление legacy login_attempts и настроек капчи из БД (перенесены в config.php).
 */
return static function (\PDO $pdo, string $prefix): void {
    $loginAttempts = $prefix . 'login_attempts';
    $settings = $prefix . 'settings';

    if (Migrate::tableExists($pdo, $loginAttempts)) {
        $pdo->exec("DROP TABLE `{$loginAttempts}`");
    }

    if (Migrate::tableExists($pdo, $settings)) {
        $keys = ['captcha_enabled', 'captcha_on_forms', 'captcha_on_login', 'captcha_length'];
        $stmt = $pdo->prepare("DELETE FROM `{$settings}` WHERE `key` = ?");
        foreach ($keys as $key) {
            $stmt->execute([$key]);
        }
    }
};
