<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class ThemeInstaller
{
    /**
     * @return array{ok: bool, error?: string, slug?: string, name?: string}
     */
    public static function install(string $zipPath): array
    {
        if (!is_file($zipPath)) {
            return ['ok' => false, 'error' => 'Файл архива не найден'];
        }

        if (!class_exists(\ZipArchive::class)) {
            return ['ok' => false, 'error' => 'Расширение ZIP не установлено в PHP'];
        }

        $workDir = STORAGE_PATH . '/theme_install_' . uniqid();
        $extractDir = $workDir . '/extracted';

        try {
            if (!is_dir(STORAGE_PATH) && !mkdir(STORAGE_PATH, 0755, true) && !is_dir(STORAGE_PATH)) {
                throw new \RuntimeException('Не удалось создать папку storage/');
            }

            if (!mkdir($extractDir, 0755, true) && !is_dir($extractDir)) {
                throw new \RuntimeException('Не удалось создать временную папку');
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('Некорректный ZIP-архив');
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if ($name === false || self::isUnsafeZipPath($name)) {
                    throw new \RuntimeException('Архив содержит недопустимые пути');
                }
            }

            if (!$zip->extractTo($extractDir)) {
                $zip->close();
                throw new \RuntimeException('Не удалось распаковать архив');
            }
            $zip->close();

            $themeRoot = self::findThemeRoot($extractDir);
            if ($themeRoot === null) {
                throw new \RuntimeException('В архиве не найден theme.json');
            }

            $slug = basename(str_replace('\\', '/', $themeRoot));
            if (!ThemeEditor::isValidSlug($slug)) {
                throw new \RuntimeException('Недопустимое имя папки темы: ' . $slug);
            }

            if (!is_file($themeRoot . '/templates/layout.php')) {
                throw new \RuntimeException('В теме отсутствует обязательный файл templates/layout.php');
            }

            $target = THEMES_PATH . '/' . $slug;
            if (is_dir($target)) {
                throw new \RuntimeException('Тема «' . $slug . '» уже установлена');
            }

            if (!is_dir(THEMES_PATH) && !mkdir(THEMES_PATH, 0755, true) && !is_dir(THEMES_PATH)) {
                throw new \RuntimeException('Не удалось создать папку content/themes/');
            }

            self::copyDirectory($themeRoot, $target);

            if (Scss::themeUsesScss($slug)) {
                Scss::compile($slug);
            }

            $meta = json_decode((string) file_get_contents($target . '/theme.json'), true);
            $name = is_array($meta) ? (string) ($meta['name'] ?? $slug) : $slug;

            return ['ok' => true, 'slug' => $slug, 'name' => $name];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        } finally {
            if (is_file($zipPath)) {
                @unlink($zipPath);
            }
            if (is_dir($workDir)) {
                ruedu_delete_directory($workDir);
            }
        }
    }

    private static function isUnsafeZipPath(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);
        if ($normalized === '' || str_starts_with($normalized, '/') || preg_match('#^[A-Za-z]:/#', $normalized)) {
            return true;
        }

        foreach (explode('/', $normalized) as $segment) {
            if ($segment === '..') {
                return true;
            }
        }

        return false;
    }

    private static function findThemeRoot(string $dir): ?string
    {
        if (is_file($dir . '/theme.json')) {
            return $dir;
        }

        $candidates = [];
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (!is_dir($path)) {
                continue;
            }
            if (is_file($path . '/theme.json')) {
                $candidates[] = $path;
            }
        }

        if (count($candidates) === 1) {
            return $candidates[0];
        }

        return null;
    }

    private static function copyDirectory(string $source, string $target): void
    {
        if (!is_dir($source)) {
            throw new \RuntimeException('Папка темы не найдена');
        }

        if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
            throw new \RuntimeException('Не удалось создать папку темы');
        }

        foreach (scandir($source) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $from = $source . '/' . $item;
            $to = $target . '/' . $item;

            if (is_dir($from)) {
                self::copyDirectory($from, $to);
                continue;
            }

            if (!copy($from, $to)) {
                throw new \RuntimeException('Не удалось скопировать файл: ' . $item);
            }
        }
    }
}
