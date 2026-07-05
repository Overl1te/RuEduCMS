<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class ThemeEditor
{
    private const EDITABLE_EXTENSIONS = ['php', 'css', 'scss', 'js', 'json', 'html', 'svg'];
    private const MAX_FILE_SIZE = 524288;

    public static function isValidSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9][a-z0-9\-]*$/', $slug);
    }

    public static function getThemeRoot(string $slug): ?string
    {
        if (!self::isValidSlug($slug)) {
            return null;
        }

        $root = THEMES_PATH . '/' . $slug;
        if (!is_dir($root)) {
            return null;
        }

        return $root;
    }

    public static function listEditableFiles(string $slug): array
    {
        $root = self::getThemeRoot($slug);
        if ($root === null) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $path = $file->getPathname();
            if (!self::isPathInside($path, $root)) {
                continue;
            }

            $relative = self::relativePath($root, $path);
            if ($relative === null || !self::isEditableExtension($relative)) {
                continue;
            }

            $files[] = $relative;
        }

        sort($files, SORT_STRING);

        return $files;
    }

    public static function readFile(string $slug, string $relativePath): ?array
    {
        $absolute = self::resolveFilePath($slug, $relativePath);
        if ($absolute === null) {
            return null;
        }

        $content = file_get_contents($absolute);
        if ($content === false) {
            return null;
        }

        return [
            'path' => self::relativePath(self::getThemeRoot($slug) ?? '', $absolute) ?? $relativePath,
            'content' => $content,
            'size' => filesize($absolute) ?: 0,
            'modified' => filemtime($absolute) ?: time(),
        ];
    }

    /**
     * @return true|string true on success, error message on failure
     */
    public static function writeFile(string $slug, string $relativePath, string $content): true|string
    {
        if (strlen($content) > self::MAX_FILE_SIZE) {
            return 'Файл слишком большой (максимум 512 КБ)';
        }

        $absolute = self::resolveFilePath($slug, $relativePath);
        if ($absolute === null) {
            return 'Файл не найден или недоступен для редактирования';
        }

        if (file_put_contents($absolute, $content) === false) {
            return 'Не удалось сохранить файл. Проверьте права на запись.';
        }

        return true;
    }

    public static function defaultFile(string $slug): ?string
    {
        $files = self::listEditableFiles($slug);
        if ($files === []) {
            return null;
        }

        foreach (['templates/home.php', 'theme.json', 'css/main.css'] as $preferred) {
            if (in_array($preferred, $files, true)) {
                return $preferred;
            }
        }

        return $files[0];
    }

    public static function modeForFile(string $relativePath): array|string
    {
        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        return match ($ext) {
            'php' => 'php',
            'css', 'scss' => 'css',
            'js' => 'javascript',
            'json' => ['name' => 'javascript', 'json' => true],
            'html' => 'htmlmixed',
            'svg' => 'xml',
            default => 'text/plain',
        };
    }

    private static function resolveFilePath(string $slug, string $relativePath): ?string
    {
        $root = self::getThemeRoot($slug);
        if ($root === null) {
            return null;
        }

        $relativePath = self::normalizeRelativePath($relativePath);
        if ($relativePath === null || !self::isEditableExtension($relativePath)) {
            return null;
        }

        $absolute = $root . '/' . $relativePath;
        if (!is_file($absolute)) {
            return null;
        }

        if (!self::isPathInside($absolute, $root)) {
            return null;
        }

        return $absolute;
    }

    private static function normalizeRelativePath(string $path): ?string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..') || str_contains($path, "\0")) {
            return null;
        }

        return $path;
    }

    private static function isEditableExtension(string $relativePath): bool
    {
        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        return in_array($ext, self::EDITABLE_EXTENSIONS, true);
    }

    private static function relativePath(string $root, string $absolute): ?string
    {
        $rootReal = realpath($root);
        $fileReal = realpath($absolute);

        if ($rootReal === false || $fileReal === false) {
            return null;
        }

        $rootNorm = rtrim(str_replace('\\', '/', $rootReal), '/');
        $fileNorm = str_replace('\\', '/', $fileReal);

        if (!str_starts_with($fileNorm, $rootNorm . '/')) {
            return null;
        }

        return substr($fileNorm, strlen($rootNorm) + 1);
    }

    private static function isPathInside(string $path, string $root): bool
    {
        $rootReal = realpath($root);
        $pathReal = realpath($path);

        if ($rootReal === false || $pathReal === false) {
            return false;
        }

        $rootNorm = rtrim(str_replace('\\', '/', $rootReal), '/');
        $pathNorm = str_replace('\\', '/', $pathReal);

        return $pathNorm === $rootNorm || str_starts_with($pathNorm, $rootNorm . '/');
    }
}
