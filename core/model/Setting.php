<?php

declare(strict_types=1);

namespace RuEdu\Model;

use RuEdu\Engine\Database;

class Setting
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $db = Database::getInstance();
        $row = $db->fetch("SELECT value FROM " . $db->table('settings') . " WHERE `key` = ?", [$key]);
        return $row ? $row['value'] : $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM " . $db->table('settings') . " WHERE `key` = ?", [$key]);

        if ($existing) {
            $db->update('settings', ['value' => $value], '`key` = ?', [$key]);
        } else {
            $db->insert('settings', ['key' => $key, 'value' => $value, 'group' => $group]);
        }
    }

    public static function getGroup(string $group): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT `key`, value FROM " . $db->table('settings') . " WHERE `group` = ?", [$group]);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }

    public static function getAll(): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT `key`, value, `group` FROM " . $db->table('settings'));
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }
}
