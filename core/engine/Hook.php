<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Hook
{
    private static array $hooks = [];

    public static function on(string $event, callable $callback, int $priority = 10): void
    {
        self::$hooks[$event][$priority][] = $callback;
    }

    public static function fire(string $event, mixed $data = null): mixed
    {
        if (!isset(self::$hooks[$event])) {
            return $data;
        }

        ksort(self::$hooks[$event]);

        foreach (self::$hooks[$event] as $callbacks) {
            foreach ($callbacks as $callback) {
                $result = $callback($data);
                if ($result !== null) {
                    $data = $result;
                }
            }
        }

        return $data;
    }

    public static function registerRoutes(Router $router): void
    {
        self::fire('register_routes', $router);
    }

    public static function registerAdminMenu(): array
    {
        return (array) self::fire('admin_menu', []);
    }

    public static function loadModules(): void
    {
        $modulesPath = MODULES_PATH;
        if (!is_dir($modulesPath)) {
            return;
        }

        $enabledNames = [];
        try {
            $db = Database::getInstance();
            $enabled = $db->fetchAll("SELECT name FROM " . $db->table('modules') . " WHERE enabled = 1");
            $enabledNames = array_column($enabled, 'name');
        } catch (\Exception $e) {
            // БД недоступна — загружаем все модули
        }

        foreach (scandir($modulesPath) as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $moduleFile = $modulesPath . '/' . $dir . '/module.php';
            if (file_exists($moduleFile) && (empty($enabledNames) || in_array($dir, $enabledNames, true))) {
                require_once $moduleFile;
            }
        }
    }
}
