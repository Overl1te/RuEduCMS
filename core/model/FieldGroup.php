<?php

declare(strict_types=1);

namespace RuEdu\Model;

use RuEdu\Engine\Database;

class FieldGroup
{
    public static function getAll(): array
    {
        $db = Database::getInstance();

        return $db->fetchAll(
            'SELECT * FROM ' . $db->table('field_groups') . ' ORDER BY sort_order ASC, title ASC'
        );
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance();

        return $db->fetch('SELECT * FROM ' . $db->table('field_groups') . ' WHERE id = ?', [$id]);
    }

    public static function getBySlug(string $slug): ?array
    {
        $db = Database::getInstance();

        return $db->fetch('SELECT * FROM ' . $db->table('field_groups') . ' WHERE slug = ?', [$slug]);
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $now = date('Y-m-d H:i:s');

        return $db->insert('field_groups', array_merge($data, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }

    public static function update(int $id, array $data): int
    {
        $db = Database::getInstance();
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $db->update('field_groups', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): int
    {
        $db = Database::getInstance();
        $db->delete('fields', 'group_id = ?', [$id]);

        return $db->delete('field_groups', 'id = ?', [$id]);
    }
}
