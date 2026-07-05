<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Router
{
    private array $routes = [];
    private string $routePrefix = '';

    public function __construct(string $routePrefix = '')
    {
        $this->routePrefix = rtrim($routePrefix, '/');
    }

    public function get(string $pattern, callable $handler): self
    {
        return $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): self
    {
        return $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable $handler): self
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
        ];

        return $this;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        $uri = rtrim($uri, '/') ?: '/';

        if ($this->routePrefix && str_starts_with($uri, $this->routePrefix)) {
            $uri = substr($uri, strlen($this->routePrefix)) ?: '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                call_user_func($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);
        echo 'Страница не найдена';
    }

    public static function getUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public static function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Базовый путь приложения (например /RuEduCMS при установке в подпапку).
     */
    public static function basePath(): string
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        if (class_exists(Config::class)) {
            $configured = Config::get('base_path');
            if (is_string($configured) && $configured !== '') {
                $cached = rtrim($configured, '/');
                return $cached;
            }
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $base = preg_replace('#/(?:admin|install)/index\.php$#', '', $scriptName);
        $base = preg_replace('#/index\.php$#', '', $base);
        $cached = rtrim($base, '/');

        return $cached;
    }

    /**
     * Публичный маршрут сайта: /sveden, /news (с учётом base_path).
     * В БД и темах хранить как /sveden — ядро нормализует при выводе.
     */
    public static function route(string $path = ''): string
    {
        if ($path === '' || $path === '/') {
            return self::path('');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            || str_starts_with($path, 'mailto:') || str_starts_with($path, 'tel:')) {
            return $path;
        }

        return self::path(ltrim($path, '/'));
    }

    /**
     * Статический ресурс: uploads, themes, admin/assets.
     */
    public static function asset(string $path = ''): string
    {
        return self::path(ltrim($path, '/'));
    }

    /**
     * Относительный URL от корня сайта (с учётом подпапки).
     */
    public static function path(string $path = ''): string
    {
        $base = self::basePath();
        $path = ltrim($path, '/');

        if ($path === '') {
            return $base !== '' ? $base . '/' : '/';
        }

        return ($base !== '' ? $base . '/' : '/') . $path;
    }

    public static function redirect(string $path, int $code = 302): void
    {
        $url = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : self::path($path);

        header('Location: ' . $url, true, $code);
        exit;
    }

    /**
     * Полный абсолютный URL (для SEO, sitemap, Open Graph).
     */
    public static function url(string $path = ''): string
    {
        $siteUrl = class_exists(Config::class) ? Config::get('site_url', '') : '';
        $pathPart = ltrim($path, '/');

        if ($siteUrl) {
            return rtrim((string) $siteUrl, '/') . ($pathPart !== '' ? '/' . $pathPart : '');
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $scheme . '://' . $host . self::route($path);
    }

    public static function detectSiteUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = self::basePath();

        return $scheme . '://' . $host . ($base !== '' ? $base : '');
    }
}
