<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class UrlSafety
{
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    public static function isSafeMenuUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || $url === '#') {
            return true;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, './') || str_starts_with($url, '../')) {
            return !str_contains(strtolower($url), 'javascript:');
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return false;
        }

        if (!isset($parts['scheme'])) {
            return true;
        }

        return in_array(strtolower((string) $parts['scheme']), self::ALLOWED_SCHEMES, true);
    }

    public static function sanitizeMenuUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || $url === '#') {
            return $url;
        }

        if (!self::isSafeMenuUrl($url)) {
            return '#';
        }

        return $url;
    }

    public static function isSafeUploadRelativePath(string $path): bool
    {
        $path = ltrim(str_replace('\\', '/', trim($path)), '/');
        if ($path === '' || str_contains($path, '..')) {
            return false;
        }

        $fullPath = UPLOADS_PATH . '/' . $path;
        if (!is_file($fullPath)) {
            return false;
        }

        $realFile = realpath($fullPath);
        $realBase = realpath(UPLOADS_PATH);

        return $realFile !== false
            && $realBase !== false
            && str_starts_with($realFile, $realBase . DIRECTORY_SEPARATOR);
    }
}
