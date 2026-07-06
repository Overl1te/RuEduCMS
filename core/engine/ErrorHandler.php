<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class ErrorHandler
{
    private static bool $registered = false;
    private static bool $handling = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$registered = true;

        $debug = self::isDebug();
        if (!$debug) {
            ini_set('display_errors', '0');
        }

        error_reporting(E_ALL);
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleException(\Throwable $e): void
    {
        self::render($e);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleShutdown(): void
    {
        if (self::$handling) {
            return;
        }

        $error = error_get_last();
        if (!$error) {
            return;
        }

        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (!in_array($error['type'], $fatalTypes, true)) {
            return;
        }

        self::render(new \ErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line']
        ));
    }

    private static function isDebug(): bool
    {
        if (!file_exists(ROOT_PATH . '/config.php')) {
            return self::isInstallRequest();
        }

        try {
            return (bool) Config::get('debug', false);
        } catch (\Throwable) {
            return false;
        }
    }

    private static function shouldShowDetails(): bool
    {
        if (self::isDebug() || self::isInstallRequest()) {
            return true;
        }

        return Session::has('user_id');
    }

    private static function isInstallRequest(): bool
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

        return str_contains($uri, '/install');
    }

    private static function isAdminRequest(): bool
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        $base = class_exists(Router::class) ? Router::basePath() : '';

        return str_contains($uri, $base . '/admin');
    }

    private static function render(\Throwable $e): void
    {
        if (self::$handling) {
            return;
        }

        self::$handling = true;
        self::log($e);

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }

        $showDetails = self::shouldShowDetails();
        $siteName = self::siteName();
        $isAdminRequest = self::isAdminRequest();

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!$showDetails && !$isAdminRequest) {
            ErrorPage::send(500);
        }

        include CORE_PATH . '/views/critical-error.php';
        exit;
    }

    private static function siteName(): string
    {
        try {
            if (class_exists(Config::class) && Config::isInstalled()) {
                return (string) Config::get('site_name', Lang::APP_NAME);
            }
        } catch (\Throwable) {
        }

        return Lang::APP_NAME;
    }

    private static function log(\Throwable $e): void
    {
        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $entry = sprintf(
            "[%s] %s: %s in %s:%d\n%s\n\n",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        @file_put_contents($logDir . '/error.log', $entry, FILE_APPEND | LOCK_EX);
    }
}
