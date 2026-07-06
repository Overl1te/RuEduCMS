<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class HelpDocs
{
    public const DEFAULT_SLUG = 'overview';

    private const GROUPS = [
        'general' => 'Общее',
        'content' => 'Контент',
        'modules' => 'Модули',
        'design' => 'Оформление',
    ];

    /**
     * @return list<array{slug: string, title: string, icon: string, group: string}>
     */
    public static function getSections(): array
    {
        return [
            ['slug' => 'overview', 'title' => 'Начало работы', 'icon' => 'bi-house-door', 'group' => 'general'],
            ['slug' => 'settings', 'title' => 'Настройки сайта', 'icon' => 'bi-gear', 'group' => 'general'],
            ['slug' => 'users', 'title' => 'Пользователи и роли', 'icon' => 'bi-people', 'group' => 'general'],
            ['slug' => 'updates', 'title' => 'Обновления', 'icon' => 'bi-arrow-repeat', 'group' => 'general'],
            ['slug' => 'pages', 'title' => 'Страницы', 'icon' => 'bi-file-earmark-text', 'group' => 'content'],
            ['slug' => 'articles', 'title' => 'Новости', 'icon' => 'bi-newspaper', 'group' => 'content'],
            ['slug' => 'media', 'title' => 'Медиабиблиотека', 'icon' => 'bi-images', 'group' => 'content'],
            ['slug' => 'menus', 'title' => 'Меню', 'icon' => 'bi-list', 'group' => 'content'],
            ['slug' => 'modules', 'title' => 'Модули', 'icon' => 'bi-puzzle', 'group' => 'modules'],
            ['slug' => 'sveden', 'title' => 'Сведения об ОО', 'icon' => 'bi-building', 'group' => 'modules'],
            ['slug' => 'staff', 'title' => 'Педагоги', 'icon' => 'bi-person-badge', 'group' => 'modules'],
            ['slug' => 'schedule', 'title' => 'Расписание', 'icon' => 'bi-calendar3', 'group' => 'modules'],
            ['slug' => 'documents', 'title' => 'Документы', 'icon' => 'bi-file-earmark-pdf', 'group' => 'modules'],
            ['slug' => 'gallery', 'title' => 'Галерея', 'icon' => 'bi-images', 'group' => 'modules'],
            ['slug' => 'contacts', 'title' => 'Контакты и заявки', 'icon' => 'bi-envelope', 'group' => 'modules'],
            ['slug' => 'themes', 'title' => 'Темы оформления', 'icon' => 'bi-palette', 'group' => 'design'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getGroups(): array
    {
        return self::GROUPS;
    }

    /**
     * @return array{slug: string, title: string, icon: string, group: string}|null
     */
    public static function getSection(string $slug): ?array
    {
        foreach (self::getSections() as $section) {
            if ($section['slug'] === $slug) {
                return $section;
            }
        }

        return null;
    }

    public static function sectionFile(string $slug): string
    {
        return CONTENT_PATH . '/docs/ru/' . $slug . '.php';
    }

    public static function sectionExists(string $slug): bool
    {
        return self::getSection($slug) !== null && is_file(self::sectionFile($slug));
    }

    public static function renderSection(string $slug): string
    {
        $file = self::sectionFile($slug);
        if (!is_file($file)) {
            return '<p class="text-muted">Раздел справки не найден.</p>';
        }

        ob_start();
        include $file;

        return (string) ob_get_clean();
    }

    /**
     * @return array<string, list<array{slug: string, title: string, icon: string, group: string}>>
     */
    public static function getGroupedSections(): array
    {
        $grouped = [];
        foreach (array_keys(self::GROUPS) as $groupKey) {
            $grouped[$groupKey] = [];
        }

        foreach (self::getSections() as $section) {
            $grouped[$section['group']][] = $section;
        }

        return $grouped;
    }
}
