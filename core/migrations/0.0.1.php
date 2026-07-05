<?php

declare(strict_types=1);

use RuEdu\Engine\Migrate;

/**
 * Логин пользователей и сброс пароля.
 */
return static function (\PDO $pdo, string $prefix): void {
    $users = $prefix . 'users';

    if (!Migrate::columnExists($pdo, $users, 'login')) {
        $pdo->exec("ALTER TABLE `{$users}` ADD COLUMN `login` VARCHAR(100) NULL AFTER `email`");
        $pdo->exec("UPDATE `{$users}` SET `login` = SUBSTRING_INDEX(`email`, '@', 1) WHERE `login` IS NULL OR `login` = ''");

        $rows = $pdo->query("SELECT id, login FROM `{$users}`")->fetchAll();
        $used = [];
        foreach ($rows as $row) {
            $login = Migrate::normalizeLogin((string) $row['login']);
            if ($login === '') {
                $login = 'user' . $row['id'];
            }
            $base = $login;
            $i = 1;
            while (isset($used[$login])) {
                $login = $base . $i;
                $i++;
            }
            $used[$login] = true;
            $stmt = $pdo->prepare("UPDATE `{$users}` SET `login` = ? WHERE id = ?");
            $stmt->execute([$login, $row['id']]);
        }

        $pdo->exec("ALTER TABLE `{$users}` MODIFY `login` VARCHAR(100) NOT NULL");
        $pdo->exec("ALTER TABLE `{$users}` ADD UNIQUE KEY `idx_login` (`login`)");
    }

    $resets = $prefix . 'password_resets';
    if (!Migrate::tableExists($pdo, $resets)) {
        $pdo->exec("CREATE TABLE `{$resets}` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `token` VARCHAR(64) NOT NULL,
            `expires_at` DATETIME NOT NULL,
            `created_at` DATETIME NOT NULL,
            UNIQUE KEY `idx_token` (`token`),
            INDEX `idx_user` (`user_id`),
            INDEX `idx_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
};
