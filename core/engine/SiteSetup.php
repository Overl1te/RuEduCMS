<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use RuEdu\Model\Setting;

class SiteSetup
{
    public const SETTING_COMPLETED = 'setup_completed';
    public const SETTING_STEP = 'setup_step';

    public const STEP_INFO = 1;
    public const STEP_MODULES = 2;
    public const STEP_ORGANIZATION = 3;
    public const STEP_FINISH = 4;

    /** @var list<string> */
    public const RECOMMENDED_MODULES = [
        'sveden',
        'news',
        'documents',
        'contacts',
        'forms',
        'staff',
        'schedule',
        'gallery',
    ];

    public static function isCompleted(): bool
    {
        return Setting::get(self::SETTING_COMPLETED) === '1';
    }

    public static function isRequired(): bool
    {
        return !self::isCompleted();
    }

    public static function markPending(): void
    {
        Setting::set(self::SETTING_COMPLETED, '0');
        Setting::set(self::SETTING_STEP, (string) self::STEP_INFO);
    }

    public static function markCompleted(): void
    {
        Setting::set(self::SETTING_COMPLETED, '1');
        Setting::set(self::SETTING_STEP, (string) self::STEP_FINISH);
        Cache::flush();
    }

    public static function getCurrentStep(): int
    {
        $step = (int) Setting::get(self::SETTING_STEP, (string) self::STEP_INFO);

        return max(self::STEP_INFO, min(self::STEP_FINISH, $step));
    }

    public static function setCurrentStep(int $step): void
    {
        Setting::set(self::SETTING_STEP, (string) max(self::STEP_INFO, min(self::STEP_FINISH, $step)));
    }

    public static function shouldShowOrganizationStep(): bool
    {
        return Modules::isEnabled('sveden');
    }

    public static function normalizeStep(int $step): int
    {
        if ($step === self::STEP_ORGANIZATION && !self::shouldShowOrganizationStep()) {
            return $step > self::STEP_ORGANIZATION ? $step : self::STEP_FINISH;
        }

        return max(self::STEP_INFO, min(self::STEP_FINISH, $step));
    }

    public static function nextStep(int $current): int
    {
        if ($current === self::STEP_MODULES && !self::shouldShowOrganizationStep()) {
            return self::STEP_FINISH;
        }

        return min(self::STEP_FINISH, $current + 1);
    }

    public static function prevStep(int $current): int
    {
        if ($current === self::STEP_FINISH && !self::shouldShowOrganizationStep()) {
            return self::STEP_MODULES;
        }

        return max(self::STEP_INFO, $current - 1);
    }

    /**
     * @return list<array{num: int, label: string, skip?: bool}>
     */
    public static function stepLabels(): array
    {
        $steps = [
            ['num' => self::STEP_INFO, 'label' => 'О сайте'],
            ['num' => self::STEP_MODULES, 'label' => 'Модули'],
            ['num' => self::STEP_ORGANIZATION, 'label' => 'Организация', 'skip' => !self::shouldShowOrganizationStep()],
            ['num' => self::STEP_FINISH, 'label' => 'Готово'],
        ];

        return array_values(array_filter($steps, static fn(array $s): bool => empty($s['skip'])));
    }

    /**
     * @return list<array{id: int, name: string, title: string, description: string, enabled: bool, recommended: bool}>
     */
    public static function getCodeModules(): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            'SELECT id, name, title, description, enabled FROM ' . $db->table('modules') . ' ORDER BY title'
        );

        $modules = [];
        foreach ($rows as $row) {
            if (!Modules::isCodeModule((string) $row['name'])) {
                continue;
            }

            $name = (string) $row['name'];
            $modules[] = [
                'id' => (int) $row['id'],
                'name' => $name,
                'title' => (string) $row['title'],
                'description' => (string) ($row['description'] ?? ''),
                'enabled' => (int) $row['enabled'] === 1,
                'recommended' => in_array($name, self::RECOMMENDED_MODULES, true),
            ];
        }

        usort($modules, static function (array $a, array $b): int {
            if ($a['recommended'] !== $b['recommended']) {
                return $a['recommended'] ? -1 : 1;
            }

            return strcmp($a['title'], $b['title']);
        });

        return $modules;
    }

    /**
     * @param array<string, mixed> $post
     * @return list<string>
     */
    public static function processStep(int $step, array $post, array $files = []): array
    {
        return match ($step) {
            self::STEP_INFO => self::processInfoStep($post, $files),
            self::STEP_MODULES => self::processModulesStep($post),
            self::STEP_ORGANIZATION => self::processOrganizationStep($post),
            self::STEP_FINISH => self::processFinishStep(),
            default => ['Неизвестный шаг настройки'],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function getInfoDefaults(): array
    {
        $settings = Setting::getAll();

        return [
            'site_name' => (string) ($settings['site_name'] ?? Config::get('site_name', '')),
            'site_description' => (string) ($settings['site_description'] ?? ''),
            'site_url' => (string) ($settings['site_url'] ?? Config::get('site_url', '')),
            'admin_email' => (string) ($settings['admin_email'] ?? Config::get('admin_email', '')),
            'contact_phone' => (string) ($settings['contact_phone'] ?? ''),
            'contact_address' => (string) ($settings['contact_address'] ?? ''),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getOrganizationDefaults(): array
    {
        $info = self::getInfoDefaults();
        $db = Database::getInstance();
        $row = $db->fetch('SELECT data FROM ' . $db->table('sveden_data') . ' WHERE section = ?', ['common']);
        $saved = $row ? (json_decode($row['data'], true) ?: []) : [];

        return [
            'full_name' => (string) ($saved['full_name'] ?? $info['site_name']),
            'short_name' => (string) ($saved['short_name'] ?? $info['site_name']),
            'address' => (string) ($saved['address'] ?? $info['contact_address']),
            'phone' => (string) ($saved['phone'] ?? $info['contact_phone']),
            'email' => (string) ($saved['email'] ?? $info['admin_email']),
            'work_schedule' => (string) ($saved['work_schedule'] ?? 'Пн–Пт: 8:00–17:00'),
        ];
    }

    /**
     * @param array<string, mixed> $post
     * @return list<string>
     */
    private static function processInfoStep(array $post, array $files): array
    {
        $errors = [];
        $siteName = trim((string) ($post['site_name'] ?? ''));
        $siteUrl = rtrim(trim((string) ($post['site_url'] ?? '')), '/');
        $adminEmail = trim((string) ($post['admin_email'] ?? ''));

        if ($siteName === '') {
            $errors[] = 'Укажите название сайта';
        }

        if ($siteUrl === '') {
            $errors[] = 'Укажите URL сайта';
        }

        if ($adminEmail !== '' && !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Некорректный email администратора';
        }

        if ($errors !== []) {
            return $errors;
        }

        if (!empty($files['site_logo']['name'])) {
            if (!SiteBranding::uploadLogo($files['site_logo'])) {
                $errors[] = 'Не удалось загрузить логотип. Допустимы PNG, JPG, GIF, WebP и ICO.';
            }
        }

        if ($errors !== []) {
            return $errors;
        }

        $keys = [
            'site_name' => $siteName,
            'site_description' => trim((string) ($post['site_description'] ?? '')),
            'site_url' => $siteUrl,
            'admin_email' => $adminEmail,
            'contact_phone' => trim((string) ($post['contact_phone'] ?? '')),
            'contact_address' => trim((string) ($post['contact_address'] ?? '')),
        ];

        foreach ($keys as $key => $value) {
            Setting::set($key, $value);
        }

        $config = Config::load();
        $config['site_name'] = $siteName;
        $config['site_url'] = $siteUrl;
        $config['site_description'] = $keys['site_description'];
        $config['admin_email'] = $adminEmail;
        Config::save($config);

        self::setCurrentStep(self::nextStep(self::STEP_INFO));

        return [];
    }

    /**
     * @param array<string, mixed> $post
     * @return list<string>
     */
    private static function processModulesStep(array $post): array
    {
        $enabled = $post['modules'] ?? [];
        if (!is_array($enabled)) {
            $enabled = [];
        }

        $enabledNames = array_map('strval', $enabled);
        $db = Database::getInstance();

        foreach (self::getCodeModules() as $module) {
            $isOn = in_array($module['name'], $enabledNames, true) ? 1 : 0;
            $db->update('modules', ['enabled' => $isOn], 'id = ?', [$module['id']]);
        }

        Modules::resetCache();
        Cache::flush();

        self::setCurrentStep(self::nextStep(self::STEP_MODULES));

        return [];
    }

    /**
     * @param array<string, mixed> $post
     * @return list<string>
     */
    private static function processOrganizationStep(array $post): array
    {
        if (!self::shouldShowOrganizationStep()) {
            self::setCurrentStep(self::STEP_FINISH);

            return [];
        }

        $fields = ['full_name', 'short_name', 'address', 'phone', 'email', 'work_schedule'];
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = trim((string) ($post[$field] ?? ''));
        }

        if ($data['full_name'] === '') {
            return ['Укажите полное наименование организации'];
        }

        if ($data['address'] === '') {
            return ['Укажите адрес организации'];
        }

        if ($data['phone'] === '') {
            return ['Укажите телефон организации'];
        }

        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['Некорректный email организации'];
        }

        $db = Database::getInstance();
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $existing = $db->fetch('SELECT id FROM ' . $db->table('sveden_data') . ' WHERE section = ?', ['common']);

        if ($existing) {
            $db->update('sveden_data', [
                'data' => $json,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'section = ?', ['common']);
        } else {
            $db->insert('sveden_data', [
                'section' => 'common',
                'data' => $json,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($data['phone'] !== '') {
            Setting::set('contact_phone', $data['phone']);
        }
        if ($data['address'] !== '') {
            Setting::set('contact_address', $data['address']);
        }

        self::setCurrentStep(self::STEP_FINISH);

        return [];
    }

    /**
     * @return list<string>
     */
    private static function processFinishStep(): array
    {
        self::markCompleted();

        return [];
    }

    public static function isExemptPath(string $uri): bool
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? $uri;
        $path = rtrim($path, '/') ?: '/';
        $base = Router::basePath();
        $adminPrefix = rtrim($base . '/admin', '/') ?: '/admin';

        if (!str_starts_with($path, $adminPrefix)) {
            return false;
        }

        $relative = substr($path, strlen($adminPrefix)) ?: '/';
        $relative = rtrim($relative, '/') ?: '/';

        return in_array($relative, ['/login', '/logout', '/forgot-password', '/reset-password', '/setup'], true)
            || str_starts_with($relative, '/reset-password');
    }
}
