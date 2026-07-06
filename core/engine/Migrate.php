<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Migrate
{
    public static function run(): void
    {
        if (!Config::isInstalled()) {
            return;
        }

        try {
            Version::normalizeLegacyDbVersion();

            $db = Database::getInstance();
            $pdo = $db->pdo();
            $prefix = Config::dbPrefix();

            self::repairSchema($pdo, $prefix);

            $current = Version::getDbVersion();

            foreach (Version::getMigrationFiles() as $version => $file) {
                if (version_compare($version, $current, '<=')) {
                    continue;
                }

                self::executeMigrationFile($file);
                Version::setDbVersion($version);
                $current = $version;
            }

            $codeVersion = Version::get();
            if (version_compare($codeVersion, $current, '>') && empty(Version::getPendingMigrations())) {
                Version::setDbVersion($codeVersion);
            }
        } catch (\Throwable $e) {
            if (Config::get('debug')) {
                throw $e;
            }
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
     * Повторный прогон миграций, если db_version опережает фактическую схему
     * (например, после установки с db_version = latest без Migrate::run).
     */
    private static function repairSchema(\PDO $pdo, string $prefix): void
    {
        $groups = $prefix . 'field_groups';
        $pages = $prefix . 'pages';

        if (!self::tableExists($pdo, $groups)) {
            self::executeMigrationByVersion('0.0.13');
        }

        if (
            self::tableExists($pdo, $groups)
            && self::tableExists($pdo, $pages)
            && !self::columnExists($pdo, $pages, 'field_data')
        ) {
            self::executeMigrationByVersion('0.0.13');
        }

        if (self::tableExists($pdo, $groups)) {
            $stmt = $pdo->query('SELECT COUNT(*) FROM `' . $groups . '`');
            $count = $stmt ? (int) $stmt->fetchColumn() : 0;
            if ($count === 0) {
                self::executeMigrationByVersion('0.0.14');
            }
        }
    }

    private static function executeMigrationByVersion(string $version): void
    {
        $file = CORE_PATH . '/migrations/' . $version . '.php';
        if (!is_file($file)) {
            return;
        }

        self::executeMigrationFile($file);

        if (version_compare(Version::getDbVersion(), $version, '<')) {
            Version::setDbVersion($version);
        }
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
