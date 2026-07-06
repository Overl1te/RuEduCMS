<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use RuEdu\Model\Setting;

class ThemeCustomizer
{
    private const SETTING_PREFIX = 'theme_customizer_';

    /** @var list<string> */
    private const GOOGLE_FONTS = [
        'Manrope',
        'Playfair Display',
        'Inter',
        'Roboto',
        'Open Sans',
        'Montserrat',
        'Nunito',
        'PT Sans',
        'PT Serif',
        'Merriweather',
        'Lora',
        'Source Sans 3',
    ];

    public static function settingKey(string $slug): string
    {
        return self::SETTING_PREFIX . $slug;
    }

    /**
     * @return array{sections: list<array<string, mixed>>}
     */
    public static function getSchema(?string $slug = null): array
    {
        $slug = $slug ?? (string) Config::get('theme', 'default-school');
        $jsonFile = THEMES_PATH . '/' . $slug . '/theme.json';

        if (!is_file($jsonFile)) {
            return ['sections' => []];
        }

        $meta = json_decode((string) file_get_contents($jsonFile), true);
        if (!is_array($meta)) {
            return ['sections' => []];
        }

        $customizer = $meta['customizer'] ?? [];
        if (!is_array($customizer)) {
            return ['sections' => []];
        }

        return $customizer;
    }

    /**
     * @return array<string, string>
     */
    public static function getDefaults(?string $slug = null): array
    {
        $schema = self::getSchema($slug);
        $defaults = [];

        foreach ($schema['sections'] ?? [] as $section) {
            if (!is_array($section)) {
                continue;
            }
            foreach ($section['fields'] ?? [] as $field) {
                if (!is_array($field) || empty($field['key'])) {
                    continue;
                }
                $key = (string) $field['key'];
                $defaults[$key] = (string) ($field['default'] ?? '');
            }
        }

        return $defaults;
    }

    /**
     * @return array<string, string>
     */
    public static function getValues(?string $slug = null): array
    {
        $slug = $slug ?? (string) Config::get('theme', 'default-school');
        $defaults = self::getDefaults($slug);
        $stored = Setting::get(self::settingKey($slug), '');

        $overrides = [];
        if (is_string($stored) && $stored !== '') {
            $decoded = json_decode($stored, true);
            if (is_array($decoded)) {
                $overrides = $decoded;
            }
        }

        $values = $defaults;
        foreach ($overrides as $key => $value) {
            if (!is_string($key) || !array_key_exists($key, $defaults)) {
                continue;
            }
            $values[$key] = (string) $value;
        }

        return $values;
    }

    /**
     * @param array<string, string> $input
     * @return true|string
     */
    public static function save(string $slug, array $input): true|string
    {
        if (!is_dir(THEMES_PATH . '/' . $slug)) {
            return 'Тема не найдена';
        }

        $defaults = self::getDefaults($slug);
        $fields = self::getFieldsMap($slug);
        $validated = [];

        foreach ($defaults as $key => $default) {
            if (!array_key_exists($key, $input)) {
                continue;
            }
            $field = $fields[$key] ?? ['type' => 'text'];
            $value = trim((string) $input[$key]);
            if ($value === '') {
                continue;
            }

            $result = self::validateField($field, $value);
            if ($result !== true) {
                return $result;
            }

            if ($value !== $default) {
                $validated[$key] = $value;
            }
        }

        Setting::set(self::settingKey($slug), json_encode($validated, JSON_UNESCAPED_UNICODE), 'theme');
        Cache::flush();

        return true;
    }

    public static function reset(string $slug): void
    {
        $db = Database::getInstance();
        $db->delete('settings', '`key` = ?', [self::settingKey($slug)]);
        Cache::flush();
    }

    public static function renderCss(?string $slug = null): string
    {
        $slug = $slug ?? (string) Config::get('theme', 'default-school');
        $defaults = self::getDefaults($slug);
        $values = self::getValues($slug);
        $fields = self::getFieldsMap($slug);
        $rules = [];

        foreach ($values as $key => $value) {
            if ($value === ($defaults[$key] ?? '')) {
                continue;
            }
            $field = $fields[$key] ?? [];
            $rules[] = $key . ': ' . self::cssValue($field, $value) . ';';
        }

        $css = $rules !== [] ? ':root {' . implode(' ', $rules) . '}' : '';
        $css = (string) Hook::fire('theme_customizer_css', $css);

        return $css;
    }

    public static function renderStyleTag(?string $slug = null): string
    {
        $css = self::renderCss($slug);
        if ($css === '') {
            return '';
        }

        return '<style id="theme-customizer">' . $css . '</style>';
    }

    public static function renderFontLinks(?string $slug = null): string
    {
        $slug = $slug ?? (string) Config::get('theme', 'default-school');
        $values = self::getValues($slug);
        $fields = self::getFieldsMap($slug);
        $fonts = [];

        foreach ($values as $key => $value) {
            $field = $fields[$key] ?? [];
            if (($field['type'] ?? '') !== 'font') {
                continue;
            }
            $name = self::extractFontName($value);
            if ($name !== '' && in_array($name, self::GOOGLE_FONTS, true)) {
                $fonts[$name] = true;
            }
        }

        if ($fonts === []) {
            return '<link rel="preconnect" href="https://fonts.googleapis.com">'
                . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
                . '<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">';
        }

        $families = array_keys($fonts);
        $query = implode('&family=', array_map(
            static fn (string $f): string => str_replace(' ', '+', $f) . ':wght@400;500;600;700;800',
            $families
        ));

        return '<link rel="preconnect" href="https://fonts.googleapis.com">'
            . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
            . '<link href="https://fonts.googleapis.com/css2?family=' . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . '&display=swap" rel="stylesheet">';
    }

    /**
     * @return list<string>
     */
    public static function getFontOptions(): array
    {
        return self::GOOGLE_FONTS;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function getFieldsMap(string $slug): array
    {
        $map = [];
        $schema = self::getSchema($slug);

        foreach ($schema['sections'] ?? [] as $section) {
            if (!is_array($section)) {
                continue;
            }
            foreach ($section['fields'] ?? [] as $field) {
                if (!is_array($field) || empty($field['key'])) {
                    continue;
                }
                $map[(string) $field['key']] = $field;
            }
        }

        return $map;
    }

    /**
     * @param array<string, mixed> $field
     * @return true|string
     */
    private static function validateField(array $field, string $value): true|string
    {
        $type = (string) ($field['type'] ?? 'text');

        return match ($type) {
            'color' => self::validateColor($value),
            'font' => self::validateFont($value),
            'range' => self::validateRange($field, $value),
            default => true,
        };
    }

    private static function validateColor(string $value): true|string
    {
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value)) {
            return true;
        }
        if (preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(\s*,\s*(0|1|0?\.\d+))?\s*\)$/', $value)) {
            return true;
        }

        return 'Недопустимый цвет: ' . $value;
    }

    private static function validateFont(string $value): true|string
    {
        $name = self::extractFontName($value);
        if ($name === '' || !in_array($name, self::GOOGLE_FONTS, true)) {
            return 'Недопустимый шрифт';
        }

        return true;
    }

    /**
     * @param array<string, mixed> $field
     */
    private static function validateRange(array $field, string $value): true|string
    {
        $unit = (string) ($field['unit'] ?? 'px');
        if (!in_array($unit, ['px', 'rem'], true)) {
            return 'Недопустимая единица';
        }

        if (!preg_match('/^\d+(\.\d+)?' . preg_quote($unit, '/') . '$/', $value)) {
            return 'Недопустимое значение';
        }

        $num = (float) rtrim($value, $unit);
        $min = (float) ($field['min'] ?? 0);
        $max = (float) ($field['max'] ?? 100);

        if ($num < $min || $num > $max) {
            return 'Значение вне диапазона';
        }

        return true;
    }

    /**
     * @param array<string, mixed> $field
     */
    private static function cssValue(array $field, string $value): string
    {
        $type = (string) ($field['type'] ?? 'text');

        if ($type === 'font') {
            $name = self::extractFontName($value);
            $stack = (string) ($field['stack'] ?? 'sans');
            $css = $stack === 'serif'
                ? "'{$name}', Georgia, serif"
                : "'{$name}', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";

            return $css;
        }

        return $value;
    }

    private static function extractFontName(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (str_contains($value, ',')) {
            $value = trim(explode(',', $value)[0]);
        }

        return trim($value, " '\"");
    }
}
