<?php

declare(strict_types=1);

namespace RuEdu\Model;

use RuEdu\Engine\Database;
use RuEdu\Engine\Modules;
use RuEdu\Engine\SEO;

class Page
{
    public static function getAll(string $status = ''): array
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . $db->table('pages');
        $params = [];

        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY sort_order ASC, title ASC";
        return $db->fetchAll($sql, $params);
    }

    public static function getBySlug(string $slug): ?array
    {
        if (!Modules::isPageEnabled($slug)) {
            return null;
        }

        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM " . $db->table('pages') . " WHERE slug = ? AND status = 'published'",
            [$slug]
        );
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM " . $db->table('pages') . " WHERE id = ?", [$id]);
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $now = date('Y-m-d H:i:s');

        if (empty($data['slug'])) {
            $data['slug'] = SEO::slugify($data['title']);
        }

        return $db->insert('pages', array_merge($data, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }

    public static function update(int $id, array $data): int
    {
        $db = Database::getInstance();
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $db->update('pages', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): int
    {
        $db = Database::getInstance();
        return $db->delete('pages', 'id = ?', [$id]);
    }
}
