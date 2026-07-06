<?php

declare(strict_types=1);

use RuEdu\Engine\Migrate;

/**
 * Блочный конструктор: content_mode и content_blocks для страниц.
 */
return static function (\PDO $pdo, string $prefix): void {
    $pages = $prefix . 'pages';

    if (!Migrate::tableExists($pdo, $pages)) {
        return;
    }

    if (!Migrate::columnExists($pdo, $pages, 'content_mode')) {
        $pdo->exec("ALTER TABLE `{$pages}` ADD COLUMN `content_mode` ENUM('html','blocks') NOT NULL DEFAULT 'html' AFTER `content`");
    }

    if (!Migrate::columnExists($pdo, $pages, 'content_blocks')) {
        $pdo->exec("ALTER TABLE `{$pages}` ADD COLUMN `content_blocks` JSON NULL AFTER `content_mode`");
    }
};
