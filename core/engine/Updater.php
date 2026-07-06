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
     * @return array{ok: bool, error?: string, version?: string, backup?: string|null}
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

        $backup = self::createBackup();
        $tempDir = STORAGE_PATH . '/update_' . uniqid();

        try {
            if (!mkdir($tempDir, 0755, true) && !is_dir($tempDir)) {
                throw new \RuntimeException('Не удалось создать временную папку');
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('Не удалось открыть архив обновления');
            }

            $zip->extractTo($tempDir);
            $zip->close();

            $root = self::findPackageRoot($tempDir);
            if ($root === null) {
                throw new \RuntimeException('В архиве не найдены папки core/ и admin/');
            }

            self::replaceDirectory($root . '/core', CORE_PATH);
            self::replaceDirectory($root . '/admin', ADMIN_PATH);

            if (is_file($root . '/VERSION')) {
                copy($root . '/VERSION', ROOT_PATH . '/VERSION');
            }

            Migrate::run();
            Cache::flush();

            return [
                'ok' => true,
                'version' => Version::get(),
                'backup' => $backup,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'backup' => $backup,
            ];
        } finally {
            if (is_dir($tempDir)) {
                ruedu_delete_directory($tempDir);
            }
            if (is_file($zipPath)) {
                @unlink($zipPath);
            }
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

        $configFile = ROOT_PATH . '/config.php';
        if (file_exists($configFile)) {
            $zip->addFile($configFile, 'config.php');
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
                if (!copy($from, $to)) {
                    throw new \RuntimeException('Не удалось скопировать файл: ' . $item);
                }
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
