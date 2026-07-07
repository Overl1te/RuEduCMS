<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Request
{
    public static function clientIp(): string
    {
        $remoteAddr = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        if ($remoteAddr === '') {
            return '0.0.0.0';
        }

        $trustedProxies = Config::get('trusted_proxies', []);
        if (!is_array($trustedProxies) || !in_array($remoteAddr, $trustedProxies, true)) {
            return $remoteAddr;
        }

        $forwarded = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
        if ($forwarded === '') {
            return $remoteAddr;
        }

        $parts = array_map('trim', explode(',', $forwarded));
        foreach ($parts as $ip) {
            if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return $remoteAddr;
    }

    public static function isAdminRequest(): bool
    {
        $uri = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');

        return str_contains($uri, '/admin');
    }

    public static function contentLength(): int
    {
        return (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    }

    public static function enforcePostSizeLimit(?int $maxBytes = null): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        if (self::isAdminRequest() || self::isInstallRequest()) {
            return;
        }

        $maxBytes ??= (int) Config::get('post_max_bytes', 10485760);
        if ($maxBytes <= 0) {
            return;
        }

        if (self::contentLength() > $maxBytes) {
            http_response_code(413);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Request entity too large';
            exit;
        }
    }

    private static function isInstallRequest(): bool
    {
        $uri = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');

        return str_contains($uri, '/install');
    }
}
