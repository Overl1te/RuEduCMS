<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Config
{
    private static ?array $data = null;
    private static string $configFile = '';

    /** Параметры, задаваемые только вручную в config.php */
    public static function manualDefaults(): array
    {
        return [
            'timezone' => 'Europe/Moscow',
            'language' => 'ru',
            'debug' => false,
            'update_source' => null,
        ];
    }

    /** Полный набор значений по умолчанию (установщик, админка, код) */
    public static function defaults(): array
    {
        return array_merge(self::manualDefaults(), [
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
            'theme' => 'default-school',
            'installed' => false,
            'secret_key' => '',
            'cache_enabled' => true,
            'db_version' => '0.0.1',
            'seo_indexing' => true,
            'indexnow_key' => '',
        ]);
    }

    public static function ensureFileExists(): bool
    {
        if (file_exists(self::configFile())) {
            return true;
        }
        return self::save(self::manualDefaults());
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

        $file = file_exists(self::configFile()) ? require self::configFile() : [];
        self::$data = array_merge(self::defaults(), is_array($file) ? $file : []);
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
