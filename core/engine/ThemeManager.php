<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use RuEdu\Model\Setting;

class ThemeManager
{
    public static function getActiveSlug(): string
    {
        return (string) Config::get('theme', 'default-school');
    }

    public static function themeExists(string $slug): bool
    {
        if (!ThemeEditor::isValidSlug($slug)) {
            return false;
        }

        return is_file(THEMES_PATH . '/' . $slug . '/theme.json');
    }

    /**
     * @return true|string true on success, error message on failure
     */
    public static function activate(string $slug): true|string
    {
        $slug = trim($slug);
        if (!self::themeExists($slug)) {
            return 'Тема не найдена';
        }

        $config = Config::load();
        $config['theme'] = $slug;
        Config::save($config);

        Setting::set('theme', $slug);
        Cache::flush();

        return true;
    }

    /**
     * @param array<string, mixed> $theme
     */
    public static function screenshotUrl(array $theme): ?string
    {
        $slug = (string) ($theme['slug'] ?? '');
        if ($slug === '' || !self::themeExists($slug)) {
            return null;
        }

        $screenshot = trim((string) ($theme['screenshot'] ?? ''));
        if ($screenshot === '') {
            return null;
        }

        $screenshot = ltrim(str_replace('\\', '/', $screenshot), '/');
        if (str_contains($screenshot, '..')) {
            return null;
        }

        $path = THEMES_PATH . '/' . $slug . '/' . $screenshot;
        if (!is_file($path)) {
            return null;
        }

        return Router::asset('themes/' . $slug . '/' . $screenshot);
    }
}
