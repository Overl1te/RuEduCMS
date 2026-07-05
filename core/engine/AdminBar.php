<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class AdminBar
{
    public static function isVisible(): bool
    {
        return Auth::check() && Auth::user() !== null;
    }

    public static function bodyClass(): string
    {
        return self::isVisible() ? ' class="has-admin-bar"' : '';
    }

    public static function render(array $context = []): string
    {
        if (!self::isVisible()) {
            return '';
        }

        $user = Auth::user();
        $items = self::getItems($context);

        ob_start();
        include CORE_PATH . '/views/admin-bar.php';
        return (string) ob_get_clean();
    }

    /**
     * @return list<array{title: string, url: string, highlight?: bool}>
     */
    private static function getItems(array $context): array
    {
        $items = [];

        if (!empty($context['page']['id'])) {
            $items[] = [
                'title' => 'Редактировать страницу',
                'url' => Router::path('admin/pages/edit/' . $context['page']['id']),
                'highlight' => true,
            ];
        } elseif (!empty($context['article']['id'])) {
            $items[] = [
                'title' => 'Редактировать новость',
                'url' => Router::path('admin/articles/edit/' . $context['article']['id']),
                'highlight' => true,
            ];
        }

        $items[] = ['title' => 'Панель', 'url' => Router::path('admin')];
        $items[] = ['title' => 'Страницы', 'url' => Router::path('admin/pages')];
        $items[] = ['title' => 'Новости', 'url' => Router::path('admin/articles')];

        if (Auth::isEditor()) {
            $items[] = ['title' => 'Медиа', 'url' => Router::path('admin/media')];
            $items[] = ['title' => 'Меню', 'url' => Router::path('admin/menus')];
        }

        if (Auth::isAdmin()) {
            $items[] = ['title' => 'Настройки', 'url' => Router::path('admin/settings')];
        }

        return (array) Hook::fire('admin_bar_items', $items);
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'Администратор',
            'editor' => 'Редактор',
            'author' => 'Автор',
            default => $role,
        };
    }
}
