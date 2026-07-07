<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class SecurityHeaders
{
    public static function apply(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        $siteUrl = (string) Config::get('site_url', '');
        if (str_starts_with($siteUrl, 'https://')) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        header('Content-Security-Policy: ' . self::contentSecurityPolicy());
    }

    private static function contentSecurityPolicy(): string
    {
        if (Request::isAdminRequest()) {
            return implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data: blob:",
                "font-src 'self' data:",
                "connect-src 'self'",
                "frame-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ]);
        }

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "frame-src https://yandex.ru https://api-maps.yandex.ru",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
    }
}
