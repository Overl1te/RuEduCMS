<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class AdminMenu
{
    public static function get(): array
    {
        $menu = [
            ['title' => 'Панель управления', 'url' => Router::path('admin'), 'icon' => 'bi-speedometer2'],
            ['title' => 'Страницы', 'url' => Router::path('admin/pages'), 'icon' => 'bi-file-earmark-text'],
            ['title' => 'Новости', 'url' => Router::path('admin/articles'), 'icon' => 'bi-newspaper'],
            ['title' => 'Медиа', 'url' => Router::path('admin/media'), 'icon' => 'bi-images'],
            ['title' => 'Меню', 'url' => Router::path('admin/menus'), 'icon' => 'bi-list'],
            ['title' => 'Пользователи', 'url' => Router::path('admin/users'), 'icon' => 'bi-people'],
            ['title' => 'Модули', 'url' => Router::path('admin/modules'), 'icon' => 'bi-puzzle'],
            ['title' => 'Обновления', 'url' => Router::path('admin/updates'), 'icon' => 'bi-arrow-repeat'],
            ['title' => 'Настройки', 'url' => Router::path('admin/settings'), 'icon' => 'bi-gear'],
        ];

        $menu = array_merge($menu, Hook::registerAdminMenu());

        foreach ($menu as &$item) {
            $item['active'] = self::isActive((string) $item['url']);
        }
        unset($item);

        return $menu;
    }

    public static function isActive(string $itemUrl): bool
    {
        $current = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        $itemPath = parse_url($itemUrl, PHP_URL_PATH) ?? $itemUrl;
        $adminRoot = Router::path('admin');
        $adminRootPath = parse_url($adminRoot, PHP_URL_PATH) ?? $adminRoot;

        $current = rtrim($current, '/') ?: '/';
        $itemPath = rtrim($itemPath, '/') ?: '/';
        $adminRootPath = rtrim($adminRootPath, '/') ?: '/';

        if ($itemPath === $adminRootPath) {
            return $current === $adminRootPath;
        }

        if ($current === $itemPath) {
            return true;
        }

        return str_starts_with($current, $itemPath . '/');
    }
}
