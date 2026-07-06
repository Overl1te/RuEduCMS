<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Config
{
    private static ?array $data = null;
    private static string $configFile = '';

    /** Базовые значения для нового config.php */
    public static function defaults(): array
    {
        return [
            'db_host' => 'localhost',
            'db_name' => '',
            'db_user' => '',
            'db_pass' => '',
            'db_prefix' => 'rc_',
            'site_url' => '',
            'base_path' => '',
            'site_name' => 'Мой сайт',
            'site_description' => 'Сайт образовательного учреждения',
            'admin_email' => '',
            'timezone' => 'Europe/Moscow',
            'theme' => 'default-school',
            'language' => 'ru',
            'debug' => false,
            'installed' => false,
            'secret_key' => '',
            'cache_enabled' => true,
            'scss_runtime' => false,
            'db_version' => '0.0.1',
            'update_source' => null,
        ];
    }

    public static function ensureFileExists(): bool
    {
        if (file_exists(self::configFile())) {
            return true;
        }
        return self::save(self::defaults());
    }

    private static function configFile(): string
    {
        if (self::$configFile === '') {
            self::$configFile = CONFIG_FILE;
        }
        return self::$configFile;
    }

    public static function load(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        if (!file_exists(self::configFile())) {
            self::$data = [];
            return self::$data;
        }

        self::$data = require self::configFile();
        return self::$data;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $data = self::load();
        return $data[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::load();
        self::$data[$key] = $value;
    }

    public static function save(array $config): bool
    {
        self::$data = $config;
        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($config, true) . ";\n";
        return (bool) file_put_contents(self::configFile(), $content, LOCK_EX);
    }

    public static function isInstalled(): bool
    {
        return file_exists(self::configFile()) && (bool) self::get('installed', false);
    }

    public static function dbPrefix(): string
    {
        return (string) self::get('db_prefix', 'rc_');
    }
}
