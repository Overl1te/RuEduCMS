<?php

declare(strict_types=1);

namespace RuEdu\Model;

use RuEdu\Engine\Database;
use RuEdu\Engine\Modules;

class Menu
{
    public static function getByLocation(string $location): array
    {
        $db = Database::getInstance();
        $menu = $db->fetch(
            "SELECT * FROM " . $db->table('menus') . " WHERE location = ?",
            [$location]
        );

        if (!$menu) {
            return [];
        }

        return self::getItems((int) $menu['id']);
    }

    public static function getItems(int $menuId, ?int $parentId = null): array
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . $db->table('menu_items') . " WHERE menu_id = ?";
        $params = [$menuId];

        if ($parentId === null) {
            $sql .= " AND parent_id IS NULL";
        } else {
            $sql .= " AND parent_id = ?";
            $params[] = $parentId;
        }

        $sql .= " ORDER BY sort_order ASC";
        $items = $db->fetchAll($sql, $params);

        foreach ($items as &$item) {
            $item['url'] = self::normalizeUrl((string) $item['url']);
            $item['children'] = self::getItems($menuId, (int) $item['id']);
        }

        return Modules::filterMenuItems($items);
    }

    private static function normalizeUrl(string $url): string
    {
        if ($url === '' || $url === '#') {
            return $url;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
            || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
            return $url;
        }

        return \RuEdu\Engine\Router::route($url);
    }

    public static function getAllMenus(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM " . $db->table('menus'));
    }

    public static function saveItems(int $menuId, array $items): void
    {
        $db = Database::getInstance();
        $db->delete('menu_items', 'menu_id = ?', [$menuId]);

        foreach ($items as $order => $item) {
            $db->insert('menu_items', [
                'menu_id' => $menuId,
                'parent_id' => $item['parent_id'] ?? null,
                'title' => $item['title'],
                'url' => $item['url'],
                'target' => $item['target'] ?? '_self',
                'sort_order' => $order,
            ]);
        }
    }
}
