<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use RuEdu\Model\Setting;

class SiteBranding
{
    private const DEFAULT_LOGO = 'core/assets/logo.png';

    private const ALLOWED_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/x-icon',
        'image/vnd.microsoft.icon',
    ];

    public static function defaultLogoPath(): string
    {
        return self::DEFAULT_LOGO;
    }

    public static function logoPath(): string
    {
        if (Config::isInstalled()) {
            $custom = trim((string) Setting::get('site_logo', ''));
            if ($custom !== '' && is_file(UPLOADS_PATH . '/' . ltrim($custom, '/'))) {
                return 'uploads/' . ltrim($custom, '/');
            }
        }

        return self::DEFAULT_LOGO;
    }

    public static function logoUrl(): string
    {
        return Router::asset(self::logoPath());
    }

    public static function faviconUrl(): string
    {
        return self::logoUrl();
    }

    public static function isCustom(): bool
    {
        if (!Config::isInstalled()) {
            return false;
        }

        $custom = trim((string) Setting::get('site_logo', ''));
        return $custom !== '' && is_file(UPLOADS_PATH . '/' . ltrim($custom, '/'));
    }

    /**
     * @return string|null Относительный путь внутри uploads/ или null при ошибке
     */
    public static function uploadLogo(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION) ?: 'png');
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico'], true)) {
            return null;
        }

        $mime = self::detectMime((string) ($file['tmp_name'] ?? ''));
        if ($mime === null || !in_array($mime, self::ALLOWED_TYPES, true)) {
            return null;
        }

        $uploadDir = UPLOADS_PATH . '/site';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $relativePath = 'site/logo.' . $ext;
        $fullPath = UPLOADS_PATH . '/' . $relativePath;

        self::deleteCustomFiles();

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return null;
        }

        Setting::set('site_logo', $relativePath);

        return $relativePath;
    }

    public static function resetLogo(): void
    {
        self::deleteCustomFiles();
        Setting::set('site_logo', '');
    }

    private static function deleteCustomFiles(): void
    {
        $custom = trim((string) Setting::get('site_logo', ''));
        if ($custom !== '') {
            $fullPath = UPLOADS_PATH . '/' . ltrim($custom, '/');
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }

        $siteDir = UPLOADS_PATH . '/site';
        if (is_dir($siteDir)) {
            foreach (glob($siteDir . '/logo.*') ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    private static function detectMime(string $tmpPath): ?string
    {
        if (!is_file($tmpPath)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return null;
        }

        $mime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        return is_string($mime) ? $mime : null;
    }
}
