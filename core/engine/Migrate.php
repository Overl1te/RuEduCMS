<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Migrate
{
    private static ?string $lastError = null;

    public static function getLastError(): ?string
    {
        return self::$lastError;
    }

    public static function run(): bool
    {
        self::$lastError = null;

        if (!Config::isInstalled()) {
            return true;
        }

        try {
            Version::normalizeLegacyDbVersion();

            $db = Database::getInstance();
            $pdo = $db->pdo();
            $prefix = Config::dbPrefix();

            $recorded = Version::getDbVersion();
            $detected = self::detectAppliedVersion($pdo, $prefix);
            if (version_compare($recorded, $detected, '<')) {
                Version::setDbVersion($detected);
            }

            self::repairSchema($pdo, $prefix);

            $current = Version::getDbVersion();
            $lastSuccess = $current;

            foreach (Version::getMigrationFiles() as $version => $file) {
                if (version_compare($version, $current, '<=')) {
                    continue;
                }

                if (self::runMigrationFile($file, $version)) {
                    Version::setDbVersion($version);
                    $current = $version;
                    $lastSuccess = $version;
                }
            }

            $codeVersion = Version::get();
            if (version_compare($codeVersion, $lastSuccess, '>') && empty(Version::getPendingMigrations())) {
                Version::setDbVersion($codeVersion);
            }

            return !Version::needsDbUpdate() && empty(Version::getPendingMigrations());
        } catch (\Throwable $e) {
            self::$lastError = $e->getMessage();
            if (Config::get('debug')) {
                throw $e;
            }

            return false;
        }
    }

    private static function runMigrationFile(string $file, string $version): bool
    {
        try {
            self::executeMigrationFile($file);

            return true;
        } catch (\Throwable $e) {
            self::$lastError = 'Миграция ' . $version . ': ' . $e->getMessage();

            return false;
        }
    }

    private static function executeMigrationFile(string $file): void
    {
        $migration = require $file;
        if (!is_callable($migration)) {
            return;
        }

        $db = Database::getInstance();
        $migration($db->pdo(), Config::dbPrefix());
    }

    /**
     * Определяет фактическую версию схемы по наличию таблиц/колонок.
     */
    public static function detectAppliedVersion(\PDO $pdo, string $prefix): string
    {
        $detected = '0.0.0';

        if (!self::tableExists($pdo, $prefix . 'users')) {
            return $detected;
        }

        $markers = [
            '0.0.1' => static fn (): bool => self::columnExists($pdo, $prefix . 'users', 'login')
                || self::tableExists($pdo, $prefix . 'password_resets'),
            '0.0.4' => static fn (): bool => self::tableExists($pdo, $prefix . 'schedule_classes'),
            '0.0.5' => static fn (): bool => self::tableExists($pdo, $prefix . 'schedule')
                && self::columnExists($pdo, $prefix . 'schedule', 'lesson_time'),
            '0.0.6' => static fn (): bool => self::tableExists($pdo, $prefix . 'pages')
                && self::pageSlugExists($pdo, $prefix, 'informaciya'),
            '0.0.7' => static fn (): bool => self::moduleExists($pdo, $prefix, 'page_informaciya'),
            '0.0.8' => static fn (): bool => self::settingExists($pdo, $prefix, 'setup_completed'),
            '0.0.12' => static fn (): bool => self::tableExists($pdo, $prefix . 'pages')
                && self::columnExists($pdo, $prefix . 'pages', 'content_blocks'),
            '0.0.13' => static fn (): bool => self::tableExists($pdo, $prefix . 'field_groups')
                && self::tableExists($pdo, $prefix . 'pages')
                && self::columnExists($pdo, $prefix . 'pages', 'field_data'),
            '0.0.14' => static fn (): bool => self::tableExists($pdo, $prefix . 'field_groups')
                && self::fieldGroupSlugExists($pdo, $prefix, 'home-page'),
            '0.0.15' => static fn (): bool => self::settingExists($pdo, $prefix, 'home_field_data'),
            '0.0.16' => static fn (): bool => self::tableExists($pdo, $prefix . 'pages')
                && self::columnExists($pdo, $prefix . 'pages', 'field_data')
                && !self::tableExists($pdo, $prefix . 'field_groups'),
            '0.0.17' => static fn (): bool => self::settingExists($pdo, $prefix, 'schema_builder_removed'),
        ];

        foreach (Version::getMigrationFiles() as $version => $_) {
            if (!isset($markers[$version])) {
                continue;
            }
            if ($markers[$version]() && version_compare($version, $detected, '>')) {
                $detected = $version;
            }
        }

        return $detected;
    }

    private static function repairSchema(\PDO $pdo, string $prefix): void
    {
        $detected = self::detectAppliedVersion($pdo, $prefix);
        if (version_compare(Version::getDbVersion(), $detected, '<')) {
            Version::setDbVersion($detected);
        }
    }

    private static function settingExists(\PDO $pdo, string $prefix, string $key): bool
    {
        $table = $prefix . 'settings';
        if (!self::tableExists($pdo, $table)) {
            return false;
        }

        $stmt = $pdo->prepare("SELECT 1 FROM `{$table}` WHERE `key` = ? LIMIT 1");
        $stmt->execute([$key]);

        return (bool) $stmt->fetchColumn();
    }

    private static function pageSlugExists(\PDO $pdo, string $prefix, string $slug): bool
    {
        $table = $prefix . 'pages';
        if (!self::tableExists($pdo, $table)) {
            return false;
        }

        $stmt = $pdo->prepare("SELECT 1 FROM `{$table}` WHERE `slug` = ? LIMIT 1");
        $stmt->execute([$slug]);

        return (bool) $stmt->fetchColumn();
    }

    private static function moduleExists(\PDO $pdo, string $prefix, string $name): bool
    {
        $table = $prefix . 'modules';
        if (!self::tableExists($pdo, $table)) {
            return false;
        }

        $stmt = $pdo->prepare("SELECT 1 FROM `{$table}` WHERE `name` = ? LIMIT 1");
        $stmt->execute([$name]);

        return (bool) $stmt->fetchColumn();
    }

    private static function fieldGroupSlugExists(\PDO $pdo, string $prefix, string $slug): bool
    {
        $table = $prefix . 'field_groups';
        if (!self::tableExists($pdo, $table)) {
            return false;
        }

        $stmt = $pdo->prepare("SELECT 1 FROM `{$table}` WHERE `slug` = ? LIMIT 1");
        $stmt->execute([$slug]);

        return (bool) $stmt->fetchColumn();
    }

    public static function normalizeLogin(string $login): string
    {
        $login = mb_strtolower(trim($login));
        $login = preg_replace('/[^a-z0-9._-]/', '', $login) ?? '';

        return substr($login, 0, 100);
    }

    public static function tableExists(\PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare(
            'SELECT 1 FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?
             LIMIT 1'
        );
        $stmt->execute([$table]);

        return (bool) $stmt->fetchColumn();
    }

    public static function columnExists(\PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare(
            'SELECT 1 FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?
             LIMIT 1'
        );
        $stmt->execute([$table, $column]);

        return (bool) $stmt->fetchColumn();
    }
}
