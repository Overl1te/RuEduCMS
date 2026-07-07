<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class RateLimiter
{
    public static function key(string $action): string
    {
        return $action . ':' . Request::clientIp();
    }

    public static function tooMany(string $action, int $maxAttempts, int $windowSeconds): bool
    {
        if (!self::tableExists()) {
            return false;
        }

        $db = Database::getInstance();
        $count = $db->count(
            'rate_limits',
            'rate_key = ? AND hit_at > DATE_SUB(NOW(), INTERVAL ? SECOND)',
            [self::key($action), $windowSeconds]
        );

        return $count >= $maxAttempts;
    }

    public static function check(string $action, int $maxAttempts, int $windowSeconds): bool
    {
        if (self::tooMany($action, $maxAttempts, $windowSeconds)) {
            SecurityLog::write('rate_limit_blocked', [
                'action' => $action,
                'max' => $maxAttempts,
                'window' => $windowSeconds,
            ]);

            return false;
        }

        return true;
    }

    public static function hit(string $action): void
    {
        if (!self::tableExists()) {
            return;
        }

        $db = Database::getInstance();
        $db->insert('rate_limits', [
            'rate_key' => self::key($action),
            'ip_address' => Request::clientIp(),
            'hit_at' => date('Y-m-d H:i:s'),
        ]);

        self::maybePurge();
    }

    public static function clear(string $action): void
    {
        if (!self::tableExists()) {
            return;
        }

        Database::getInstance()->delete('rate_limits', 'rate_key = ?', [self::key($action)]);
    }

    private static function maybePurge(): void
    {
        if (random_int(1, 20) !== 1) {
            return;
        }

        $hours = max(1, (int) Config::get('rate_limit_cleanup_hours', 24));
        try {
            Database::getInstance()->delete(
                'rate_limits',
                'hit_at < DATE_SUB(NOW(), INTERVAL ? HOUR)',
                [$hours]
            );
        } catch (\Throwable) {
            // ignore cleanup errors
        }
    }

    private static function tableExists(): bool
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }

        try {
            if (!Config::isInstalled()) {
                $exists = false;

                return false;
            }

            Config::load();
            $db = Database::getInstance();
            $db->fetch('SELECT 1 FROM ' . $db->table('rate_limits') . ' LIMIT 1');
            $exists = true;
        } catch (\Throwable) {
            $exists = false;
        }

        return $exists;
    }
}
