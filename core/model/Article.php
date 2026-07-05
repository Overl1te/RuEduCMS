<?php

declare(strict_types=1);

namespace RuEdu\Model;

use RuEdu\Engine\Database;
use RuEdu\Engine\SEO;

class Article
{
    public static function getAll(string $status = '', int $limit = 0, int $offset = 0): array
    {
        $db = Database::getInstance();
        $sql = "SELECT a.*, c.name as category_name FROM " . $db->table('articles') . " a
                LEFT JOIN " . $db->table('article_categories') . " c ON a.category_id = c.id";
        $params = [];
        $conditions = [];

        if ($status) {
            $conditions[] = "a.status = ?";
            $params[] = $status;
        }

        if ($conditions) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY a.published_at DESC, a.created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        return $db->fetchAll($sql, $params);
    }

    public static function getBySlug(string $slug): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT a.*, c.name as category_name FROM " . $db->table('articles') . " a
             LEFT JOIN " . $db->table('article_categories') . " c ON a.category_id = c.id
             WHERE a.slug = ? AND a.status = 'published'",
            [$slug]
        );
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM " . $db->table('articles') . " WHERE id = ?", [$id]);
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $now = date('Y-m-d H:i:s');

        if (empty($data['slug'])) {
            $data['slug'] = SEO::slugify($data['title']);
        }

        return $db->insert('articles', array_merge($data, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }

    public static function update(int $id, array $data): int
    {
        $db = Database::getInstance();
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $db->update('articles', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): int
    {
        $db = Database::getInstance();
        return $db->delete('articles', 'id = ?', [$id]);
    }

    public static function getCategories(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM " . $db->table('article_categories') . " ORDER BY sort_order");
    }
}
