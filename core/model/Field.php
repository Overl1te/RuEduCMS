<?php

declare(strict_types=1);

namespace RuEdu\Model;

use RuEdu\Engine\Database;

class Field
{
    public static function getByGroupId(int $groupId): array
    {
        $db = Database::getInstance();

        return $db->fetchAll(
            'SELECT * FROM ' . $db->table('fields') . ' WHERE group_id = ? ORDER BY sort_order ASC, id ASC',
            [$groupId]
        );
    }

    public static function deleteByGroupId(int $groupId): void
    {
        $db = Database::getInstance();
        $db->delete('fields', 'group_id = ?', [$groupId]);
    }

    public static function insert(array $data): int
    {
        $db = Database::getInstance();

        return $db->insert('fields', $data);
    }
}
