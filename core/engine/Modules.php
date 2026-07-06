<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Modules
{
    private const SECTION_PREFIX = 'section-';

    /** @var array<string, bool>|null */
    private static ?array $enabledMap = null;

    /** @var array<string, bool>|null */
    private static ?array $existsMap = null;

    public static function sectionName(string $slug): string
    {
        return self::SECTION_PREFIX . $slug;
    }

    public static function isSectionModule(string $name): bool
    {
        return str_starts_with($name, self::SECTION_PREFIX);
    }

    public static function isCodeModule(string $name): bool
    {
        return is_file(MODULES_PATH . '/' . $name . '/module.php');
    }

    public static function exists(string $name): bool
    {
        self::loadMaps();

        return self::$existsMap[$name] ?? false;
    }

    public static function isEnabled(?string $name): bool
    {
        if ($name === null || $name === '') {
            return true;
        }

        self::loadMaps();

        return self::$enabledMap[$name] ?? false;
    }

    public static function moduleForUrl(string $url): ?string
    {
        if ($url === '' || $url === '#' || str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return null;
        }

        $path = trim(parse_url($url, PHP_URL_PATH) ?: $url, '/');

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'page/')) {
            $slug = substr($path, 5);

            return $slug !== '' ? self::sectionName($slug) : null;
        }

        $first = explode('/', $path)[0];

        return match ($first) {
            'sveden', 'news', 'staff', 'schedule', 'documents', 'gallery', 'contacts' => $first,
            default => null,
        };
    }

    public static function isUrlEnabled(string $url): bool
    {
        return self::isEnabled(self::moduleForUrl($url));
    }

    public static function isPageEnabled(string $slug): bool
    {
        $module = self::sectionName($slug);

        if (!self::exists($module)) {
            return true;
        }

        return self::isEnabled($module);
    }

    /**
     * @param list<array{title: string, url: string, target?: string, children?: list<mixed>, module?: ?string}> $items
     * @return list<array{title: string, url: string, target?: string, children?: list<mixed>}>
     */
    public static function filterMenuItems(array $items): array
    {
        $filtered = [];

        foreach ($items as $item) {
            $module = $item['module'] ?? self::moduleForUrl($item['url'] ?? '');
            if (!$module || self::isEnabled($module)) {
                if (!empty($item['children'])) {
                    $item['children'] = self::filterMenuItems($item['children']);
                }

                if ($module || !empty($item['children']) || ($item['url'] ?? '#') !== '#') {
                    $filtered[] = $item;
                }
            }
        }

        return $filtered;
    }

    public static function resetCache(): void
    {
        self::$enabledMap = null;
        self::$existsMap = null;
    }

    private static function loadMaps(): void
    {
        if (self::$enabledMap !== null) {
            return;
        }

        self::$enabledMap = [];
        self::$existsMap = [];

        try {
            $db = Database::getInstance();
            $rows = $db->fetchAll('SELECT name, enabled FROM ' . $db->table('modules'));
            foreach ($rows as $row) {
                $name = (string) $row['name'];
                self::$existsMap[$name] = true;
                self::$enabledMap[$name] = (int) $row['enabled'] === 1;
            }
        } catch (\Throwable) {
            // БД недоступна
        }
    }
}
