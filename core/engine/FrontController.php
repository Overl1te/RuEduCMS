<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class FrontController
{
    /** @var array<string, string> */
    private const ALIASES = [
        'themes/' => 'content/themes/',
        'uploads/' => 'content/uploads/',
        'core/assets/' => 'core/assets/',
    ];

    private const DENIED_PREFIXES = [
        'config/',
        'core/',
        'storage/',
        'content/modules/',
    ];

    private const PUBLIC_CORE_PREFIX = 'core/assets/';

    /**
     * Обработка запроса для nginx (и любого сервера без mod_rewrite).
     *
     * @return bool true, если ответ уже отправлен
     */
    public static function handle(): bool
    {
        $relative = self::relativePath();

        if (preg_match('#^themes/([a-z0-9][a-z0-9\-]*)/style\.css$#', $relative, $matches)) {
            $slug = $matches[1];
            if (!ThemeEditor::isValidSlug($slug) || !Scss::themeUsesScss($slug)) {
                ErrorPage::send(404);
                return true;
            }

            if (Config::isInstalled()) {
                Config::load();
            }

            Scss::serve($slug);
            return true;
        }

        if (self::isDenied($relative)) {
            ErrorPage::send(403);
            return true;
        }

        if (self::serveStatic($relative)) {
            return true;
        }

        if ($relative === 'error.php') {
            ErrorPage::send(ErrorPage::detectCode());
            return true;
        }

        if ($relative === 'install' || str_starts_with($relative, 'install/')) {
            require ROOT_PATH . '/install/index.php';
            return true;
        }

        if (
            ($relative === 'admin' || str_starts_with($relative, 'admin/'))
            && !str_starts_with($relative, 'admin/assets/')
        ) {
            require ADMIN_PATH . '/index.php';
            return true;
        }

        return false;
    }

    private static function relativePath(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $path = rawurldecode($uri);
        $base = Router::basePath();

        if ($base !== '' && str_starts_with($path, $base . '/')) {
            return ltrim(substr($path, strlen($base)), '/');
        }

        if ($base !== '' && $path === $base) {
            return '';
        }

        return ltrim($path, '/');
    }

    private static function isDenied(string $relative): bool
    {
        if ($relative === 'config.php') {
            return true;
        }

        if (str_starts_with($relative, self::PUBLIC_CORE_PREFIX)) {
            return false;
        }

        foreach (self::DENIED_PREFIXES as $prefix) {
            if ($relative === rtrim($prefix, '/') || str_starts_with($relative, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private static function serveStatic(string $relative): bool
    {
        foreach (self::ALIASES as $prefix => $target) {
            if (!str_starts_with($relative, $prefix)) {
                continue;
            }

            $file = ROOT_PATH . '/' . $target . substr($relative, strlen($prefix));
            if (self::sendFile($file, [ROOT_PATH . '/' . $target])) {
                return true;
            }

            ErrorPage::send(404);
            return true;
        }

        if (str_starts_with($relative, 'admin/assets/')) {
            if (self::sendFile(ROOT_PATH . '/' . $relative, [ROOT_PATH . '/admin/assets'])) {
                return true;
            }

            ErrorPage::send(404);
            return true;
        }

        return false;
    }

    /**
     * @param list<string> $allowedRoots
     */
    private static function sendFile(string $file, array $allowedRoots): bool
    {
        $real = realpath($file);
        if ($real === false || !is_file($real)) {
            return false;
        }

        $normalized = str_replace('\\', '/', $real);
        $allowed = false;

        foreach ($allowedRoots as $root) {
            $rootReal = realpath($root);
            if ($rootReal === false) {
                continue;
            }

            $rootPrefix = rtrim(str_replace('\\', '/', $rootReal), '/') . '/';
            if (str_starts_with($normalized, $rootPrefix)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return false;
        }

        if (str_ends_with(strtolower($normalized), '.php')) {
            ErrorPage::send(403);
            return true;
        }

        $mime = mime_content_type($real) ?: 'application/octet-stream';
        if (!headers_sent()) {
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . (string) filesize($real));
        }

        readfile($real);
        return true;
    }
}
