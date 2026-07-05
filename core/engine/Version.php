<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Version
{
    private static ?string $version = null;

    /** @var array<string, string> */
    private const LEGACY_DB_VERSION_MAP = [
        '1.0.0' => '0.0.0',
        '1.0.1' => '0.0.1',
    ];

    public static function get(): string
    {
        if (self::$version === null) {
            $file = ROOT_PATH . '/VERSION';
            if (is_file($file)) {
                self::$version = trim((string) file_get_contents($file)) ?: '0.0.1';
            } else {
                self::$version = '0.0.1';
            }
        }

        return self::$version;
    }

    public static function getDbVersion(): string
    {
        return (string) Config::get('db_version', '0.0.0');
    }

    public static function normalizeLegacyDbVersion(): void
    {
        $current = self::getDbVersion();
        if (isset(self::LEGACY_DB_VERSION_MAP[$current])) {
            self::setDbVersion(self::LEGACY_DB_VERSION_MAP[$current]);
        }
    }

    public static function getLatestMigrationVersion(): string
    {
        $migrations = self::getMigrationFiles();

        if ($migrations === []) {
            return self::get();
        }

        return (string) array_key_last($migrations);
    }

    public static function setDbVersion(string $version): void
    {
        $config = Config::load();
        $config['db_version'] = $version;
        Config::save($config);
        Config::set('db_version', $version);
    }

    public static function needsDbUpdate(): bool
    {
        if (!Config::isInstalled()) {
            return false;
        }

        return version_compare(self::get(), self::getDbVersion(), '>');
    }

    /**
     * @return array<string, string> version => file path
     */
    public static function getMigrationFiles(): array
    {
        $dir = CORE_PATH . '/migrations';
        if (!is_dir($dir)) {
            return [];
        }

        $migrations = [];
        foreach (scandir($dir) as $file) {
            if (!preg_match('/^(\d+\.\d+\.\d+)\.php$/', $file, $m)) {
                continue;
            }
            $migrations[$m[1]] = $dir . '/' . $file;
        }

        uksort($migrations, 'version_compare');

        return $migrations;
    }

    /**
     * @return list<string>
     */
    public static function getPendingMigrations(): array
    {
        $current = self::getDbVersion();
        $pending = [];

        foreach (self::getMigrationFiles() as $version => $file) {
            if (version_compare($version, $current, '>')) {
                $pending[] = $version;
            }
        }

        return $pending;
    }

    public static function isUpToDate(): bool
    {
        return !self::needsDbUpdate() && empty(self::getPendingMigrations());
    }
}
