<?php

declare(strict_types=1);

use RuEdu\Engine\Migrate;

/**
 * Удаление конструкторов и групп полей — вёрстка перенесена в темы.
 */
return static function (\PDO $pdo, string $prefix): void {
    $fields = $prefix . 'fields';
    $groups = $prefix . 'field_groups';
    $settings = $prefix . 'settings';
    $pages = $prefix . 'pages';

    if (Migrate::tableExists($pdo, $fields)) {
        $pdo->exec("DROP TABLE `{$fields}`");
    }

    if (Migrate::tableExists($pdo, $groups)) {
        $pdo->exec("DROP TABLE `{$groups}`");
    }

    if (Migrate::tableExists($pdo, $settings)) {
        $pdo->exec("DELETE FROM `{$settings}` WHERE `key` IN ('home_field_data', 'home_layout') OR `key` LIKE 'system_field_data_%'");
    }

    if (Migrate::tableExists($pdo, $pages) && Migrate::columnExists($pdo, $pages, 'content_mode')) {
        $pdo->exec("UPDATE `{$pages}` SET `content_mode` = 'html' WHERE `content_mode` IN ('blocks', 'fields')");
    }
};
