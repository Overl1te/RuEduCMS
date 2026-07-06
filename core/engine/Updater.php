<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Updater
{
    public static function checkForUpdate(): ?array
    {
        $source = Config::get('update_source');
        if (!$source) {
            return null;
        }

        if ($source === 'github') {
            return self::checkGitHubUpdate();
        }

        return null;
    }

    private static function checkGitHubUpdate(): ?array
    {
        $repo = Config::get('update_github_repo', 'RuEduCMS/RuEduCMS');
        $url = 'https://api.github.com/repos/' . $repo . '/releases/latest';

        $context = stream_context_create([
            'http' => ['header' => "User-Agent: RuEduCMS/" . Version::get() . "\r\n"],
        ]);

        $response = @file_get_contents($url, false, $context);
        if (!$response) {
            return null;
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['tag_name'])) {
            return null;
        }

        $latest = ltrim($data['tag_name'], 'v');
        if (version_compare($latest, Version::get(), '>')) {
            return [
                'version' => $latest,
                'url' => $data['html_url'] ?? '',
                'download' => $data['zipball_url'] ?? '',
                'notes' => $data['body'] ?? '',
                'source' => 'github',
            ];
        }

        return null;
    }

    /**
     * @return array{ok: bool, error?: string, version?: string, backup?: string|null, staged?: bool}
     */
    public static function applyFromZip(string $zipPath): array
    {
        if (!class_exists(\ZipArchive::class)) {
            return ['ok' => false, 'error' => 'Расширение ZIP не установлено в PHP'];
        }

        $validation = self::validatePackage($zipPath);
        if (!$validation['ok']) {
            return $validation;
        }

        $writableCheck = self::ensureWritableTargets();
        if (!$writableCheck['ok']) {
            return $writableCheck;
        }

        $backup = self::createBackup();
        $pendingDir = STORAGE_PATH . '/pending_update';
        $extractDir = $pendingDir . '/extracted';

        try {
            if (is_dir($pendingDir)) {
                ruedu_delete_directory($pendingDir);
            }
            if (!mkdir($extractDir, 0755, true) && !is_dir($extractDir)) {
                throw new \RuntimeException('Не удалось создать папку для обновления');
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('Не удалось открыть архив обновления');
            }

            $zip->extractTo($extractDir);
            $zip->close();

            $root = self::findPackageRoot($extractDir);
            if ($root === null) {
                throw new \RuntimeException('В архиве не найдены папки core/ и admin/');
            }

            $manifest = [
                'root' => $root,
                'version' => $validation['version'] ?? null,
                'backup' => $backup,
                'staged_at' => date('c'),
            ];

            if (file_put_contents(
                $pendingDir . '/manifest.json',
                json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            ) === false) {
                throw new \RuntimeException('Не удалось сохранить манифест обновления');
            }

            return [
                'ok' => true,
                'staged' => true,
                'version' => $validation['version'] ?? null,
                'backup' => $backup,
            ];
        } catch (\Throwable $e) {
            if (is_dir($pendingDir)) {
                ruedu_delete_directory($pendingDir);
            }

            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'backup' => $backup,
            ];
        } finally {
            if (is_file($zipPath)) {
                @unlink($zipPath);
            }
        }
    }

    /**
     * Применяет отложенное обновление до загрузки admin/index.php (важно для Windows).
     *
     * @return array{ok: bool, error?: string, version?: string}|null
     */
    public static function applyPending(): ?array
    {
        $pendingDir = STORAGE_PATH . '/pending_update';
        $manifestFile = $pendingDir . '/manifest.json';

        if (!is_file($manifestFile)) {
            return null;
        }

        $lockFile = $pendingDir . '/.lock';
        $lock = @fopen($lockFile, 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            if ($lock !== false) {
                fclose($lock);
            }
            return null;
        }

        $applied = false;

        try {
            if (!is_file($manifestFile)) {
                return null;
            }

            $manifest = json_decode((string) file_get_contents($manifestFile), true);
            $root = is_array($manifest) ? ($manifest['root'] ?? null) : null;
            if (!is_string($root) || !is_dir($root . '/core') || !is_dir($root . '/admin')) {
                throw new \RuntimeException('Пакет обновления повреждён или неполный');
            }

            $writableCheck = self::ensureWritableTargets();
            if (!$writableCheck['ok']) {
                throw new \RuntimeException($writableCheck['error'] ?? 'Нет прав на запись');
            }

            self::replaceDirectory($root . '/core', CORE_PATH);
            self::replaceDirectory($root . '/admin', ADMIN_PATH);

            if (is_file($root . '/VERSION')) {
                self::copyFile($root . '/VERSION', ROOT_PATH . '/VERSION');
                Version::reset();
            }

            Migrate::run();
            Cache::flush();
            $applied = true;

            return [
                'ok' => true,
                'version' => Version::get(),
            ];
        } catch (\Throwable $e) {
            error_log('RuEduCMS update failed: ' . $e->getMessage());

            return [
                'ok' => false,
                'error' => $e->getMessage(),
            ];
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);

            if ($applied) {
                self::clearPending($pendingDir);
            }
        }
    }

    public static function hasPendingUpdate(): bool
    {
        return is_file(STORAGE_PATH . '/pending_update/manifest.json');
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    private static function ensureWritableTargets(): array
    {
        foreach ([CORE_PATH => 'core/', ADMIN_PATH => 'admin/', ROOT_PATH . '/VERSION' => 'VERSION'] as $path => $label) {
            $dir = is_file($path) ? dirname($path) : $path;
            if (!is_dir($dir)) {
                return ['ok' => false, 'error' => 'Папка ' . $label . ' не найдена'];
            }
            if (!is_writable($dir)) {
                return ['ok' => false, 'error' => 'Нет прав на запись в ' . $label];
            }
        }

        return ['ok' => true];
    }

    private static function clearPending(string $pendingDir): void
    {
        if (!is_dir($pendingDir)) {
            return;
        }

        if (!ruedu_delete_directory($pendingDir) && is_dir($pendingDir)) {
            error_log('RuEduCMS update cleanup failed: could not remove ' . $pendingDir);
        }
    }

    /**
     * @return array{ok: bool, error?: string, version?: string}
     */
    public static function validatePackage(string $zipPath): array
    {
        if (!is_file($zipPath)) {
            return ['ok' => false, 'error' => 'Файл архива не найден'];
        }

        if (!class_exists(\ZipArchive::class)) {
            return ['ok' => false, 'error' => 'Расширение ZIP не установлено в PHP'];
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['ok' => false, 'error' => 'Некорректный ZIP-архив'];
        }

        $hasCore = false;
        $hasAdmin = false;
        $packageVersion = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false) {
                continue;
            }

            if (preg_match('#(^|/)core/#', $name)) {
                $hasCore = true;
            }
            if (preg_match('#(^|/)admin/#', $name)) {
                $hasAdmin = true;
            }
            if (preg_match('#(^|/)VERSION$#', $name)) {
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    $packageVersion = trim($content);
                }
            }
        }

        $zip->close();

        if (!$hasCore || !$hasAdmin) {
            return ['ok' => false, 'error' => 'Архив должен содержать папки core/ и admin/'];
        }

        if ($packageVersion !== null && version_compare($packageVersion, Version::get(), '<=')) {
            return ['ok' => false, 'error' => 'Версия в архиве (' . $packageVersion . ') не новее текущей (' . Version::get() . ')'];
        }

        return ['ok' => true, 'version' => $packageVersion ?? 'неизвестна'];
    }

    public static function createBackup(): ?string
    {
        if (!class_exists(\ZipArchive::class)) {
            return null;
        }

        $backupDir = STORAGE_PATH . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupFile = $backupDir . '/backup_' . date('Y-m-d_H-i-s') . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($backupFile, \ZipArchive::CREATE) !== true) {
            return null;
        }

        if (file_exists(CONFIG_FILE)) {
            $zip->addFile(CONFIG_FILE, 'config.php');
        }

        self::addDirToZip($zip, UPLOADS_PATH, 'uploads');
        self::addDirToZip($zip, CORE_PATH, 'core');
        self::addDirToZip($zip, ADMIN_PATH, 'admin');

        if (is_file(ROOT_PATH . '/VERSION')) {
            $zip->addFile(ROOT_PATH . '/VERSION', 'VERSION');
        }

        $zip->close();

        return $backupFile;
    }

    /**
     * @return list<array{file: string, size: int, date: string}>
     */
    public static function listBackups(): array
    {
        $backupDir = STORAGE_PATH . '/backups';
        if (!is_dir($backupDir)) {
            return [];
        }

        $backups = [];
        foreach (scandir($backupDir) as $file) {
            if (!str_ends_with($file, '.zip')) {
                continue;
            }
            $path = $backupDir . '/' . $file;
            $backups[] = [
                'file' => $file,
                'size' => (int) filesize($path),
                'date' => date('d.m.Y H:i', (int) filemtime($path)),
            ];
        }

        usort($backups, fn ($a, $b) => strcmp($b['file'], $a['file']));

        return $backups;
    }

    private static function findPackageRoot(string $dir): ?string
    {
        if (is_dir($dir . '/core') && is_dir($dir . '/admin')) {
            return $dir;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (!is_dir($path)) {
                continue;
            }
            $found = self::findPackageRoot($path);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Обновляет папку пофайлово, без rename() всей директории.
     * Так можно обновлять admin/ и core/, пока CMS уже запущена (Windows, php-fpm).
     */
    private static function replaceDirectory(string $source, string $target): void
    {
        if (!is_dir($source)) {
            throw new \RuntimeException('Папка не найдена: ' . basename($source));
        }

        self::syncDirectory($source, $target);
    }

    private static function syncDirectory(string $source, string $target): void
    {
        if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
            throw new \RuntimeException('Не удалось создать папку: ' . basename($target));
        }

        foreach (scandir($source) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $from = $source . '/' . $item;
            $to = $target . '/' . $item;
            if (is_dir($from)) {
                if (is_file($to)) {
                    unlink($to);
                }
                self::syncDirectory($from, $to);
            } else {
                if (is_dir($to)) {
                    ruedu_delete_directory($to);
                }
                self::copyFile($from, $to);
            }
        }

        foreach (scandir($target) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            if (!file_exists($source . '/' . $item)) {
                $path = $target . '/' . $item;
                if (is_dir($path)) {
                    ruedu_delete_directory($path);
                } else {
                    @unlink($path);
                }
            }
        }
    }

    private static function copyFile(string $from, string $to): void
    {
        if (@copy($from, $to)) {
            self::invalidateOpcache($to);
            return;
        }

        $data = file_get_contents($from);
        if ($data === false) {
            throw new \RuntimeException('Не удалось прочитать файл: ' . basename($from));
        }

        if (file_put_contents($to, $data) === false) {
            throw new \RuntimeException('Не удалось скопировать файл: ' . basename($to));
        }

        self::invalidateOpcache($to);
    }

    private static function invalidateOpcache(string $file): void
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
    }

    private static function addDirToZip(\ZipArchive $zip, string $dir, string $zipPath): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $fullPath = $dir . '/' . $file;
            $archivePath = $zipPath . '/' . $file;
            if (is_dir($fullPath)) {
                self::addDirToZip($zip, $fullPath, $archivePath);
            } else {
                $zip->addFile($fullPath, $archivePath);
            }
        }
    }
}
