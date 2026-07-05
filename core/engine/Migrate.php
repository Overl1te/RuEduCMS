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
            $current = Version::getDbVersion();

            foreach (Version::getMigrationFiles() as $version => $file) {
                if (version_compare($version, $current, '<=')) {
                    continue;
                }

                $migration = require $file;
                if (!is_callable($migration)) {
                    continue;
                }

                $migration($pdo, $prefix);
                Version::setDbVersion($version);
                $current = $version;
            }

            $codeVersion = Version::get();
            if (version_compare($codeVersion, $current, '>') && empty(Version::getPendingMigrations())) {
                Version::setDbVersion($codeVersion);
            }
        } catch (\Throwable) {
            // Миграция не должна ломать работу сайта
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
