<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class SystemPages
{
    /**
     * @return list<array{
     *     id: string,
     *     title: string,
     *     url: string,
     *     template: string,
     *     module: ?string,
     *     content_url: ?string,
     *     sort_order: int
     * }>
     */
    public static function getAll(): array
    {
        $pages = self::definitions();
        $enabledModules = self::enabledModules();

        $result = [];
        foreach ($pages as $page) {
            $module = $page['module'] ?? null;
            if ($module !== null && !in_array($module, $enabledModules, true)) {
                $page['enabled'] = false;
            } else {
                $page['enabled'] = true;
            }

            $result[] = $page;
        }

        return Hook::fire('system_pages', $result);
    }

    /**
     * @return list<string>
     */
    private static function enabledModules(): array
    {
        try {
            $db = Database::getInstance();
            $rows = $db->fetchAll('SELECT name FROM ' . $db->table('modules') . ' WHERE enabled = 1');

            return array_column($rows, 'name');
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * @return list<array{
     *     id: string,
     *     title: string,
     *     url: string,
     *     template: string,
     *     module: ?string,
     *     content_url: ?string,
     *     sort_order: int
     * }>
     */
    private static function definitions(): array
    {
        return [
            [
                'id' => 'home',
                'title' => 'Главная',
                'url' => '/',
                'template' => 'templates/home.php',
                'module' => null,
                'content_url' => null,
                'sort_order' => 1,
            ],
            [
                'id' => 'sveden',
                'title' => 'Сведения об ОО',
                'url' => '/sveden',
                'template' => 'templates/sveden.php',
                'module' => 'sveden',
                'content_url' => 'admin/sveden',
                'sort_order' => 2,
            ],
            [
                'id' => 'news',
                'title' => 'Новости',
                'url' => '/news',
                'template' => 'templates/news-list.php',
                'module' => 'news',
                'content_url' => 'admin/articles',
                'sort_order' => 3,
            ],
            [
                'id' => 'staff',
                'title' => 'Педагогический состав',
                'url' => '/staff',
                'template' => 'templates/staff.php',
                'module' => 'staff',
                'content_url' => 'admin/staff',
                'sort_order' => 4,
            ],
            [
                'id' => 'schedule',
                'title' => 'Расписание',
                'url' => '/schedule',
                'template' => 'templates/schedule.php',
                'module' => 'schedule',
                'content_url' => 'admin/schedule',
                'sort_order' => 5,
            ],
            [
                'id' => 'documents',
                'title' => 'Документы',
                'url' => '/documents',
                'template' => 'templates/documents.php',
                'module' => 'documents',
                'content_url' => 'admin/documents',
                'sort_order' => 6,
            ],
            [
                'id' => 'gallery',
                'title' => 'Галерея',
                'url' => '/gallery',
                'template' => 'templates/gallery.php',
                'module' => 'gallery',
                'content_url' => 'admin/gallery',
                'sort_order' => 7,
            ],
            [
                'id' => 'contacts',
                'title' => 'Контакты',
                'url' => '/contacts',
                'template' => 'templates/contacts.php',
                'module' => 'contacts',
                'content_url' => 'admin/settings',
                'sort_order' => 8,
            ],
        ];
    }
}
