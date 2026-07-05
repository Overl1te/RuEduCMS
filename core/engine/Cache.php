<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Cache
{
    private static string $cacheDir = STORAGE_PATH . '/cache';

    public static function get(string $key): mixed
    {
        if (!Config::get('cache_enabled', true)) {
            return null;
        }

        $file = self::getFile($key);
        if (!file_exists($file)) {
            return null;
        }

        $data = unserialize(file_get_contents($file));
        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    public static function set(string $key, mixed $value, int $ttl = 3600): void
    {
        if (!Config::get('cache_enabled', true)) {
            return;
        }

        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }

        $data = ['value' => $value, 'expires' => time() + $ttl];
        file_put_contents(self::getFile($key), serialize($data), LOCK_EX);
    }

    public static function delete(string $key): void
    {
        $file = self::getFile($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public static function flush(): void
    {
        if (!is_dir(self::$cacheDir)) {
            return;
        }
        foreach (glob(self::$cacheDir . '/*') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private static function getFile(string $key): string
    {
        return self::$cacheDir . '/' . md5($key) . '.cache';
    }
}
