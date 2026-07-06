<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class YandexMap
{
    private const HEIGHT = 450;

    /**
     * Карта из настроек сайта (кэш на один запрос).
     */
    public static function fromSettings(): string
    {
        static $cached;

        if ($cached === null) {
            $cached = self::embedHtml((string) \RuEdu\Model\Setting::get('yandex_map', ''));
        }

        return $cached;
    }

    /**
     * Безопасный HTML для вставки карты.
     */
    public static function embedHtml(string $raw): string
    {
        $raw = self::normalizeInput($raw);
        if ($raw === '') {
            return '';
        }

        if (preg_match('/<iframe\b/i', $raw)) {
            return self::buildIframe(self::extractIframeSrc($raw) ?? '');
        }

        if (preg_match('/<script\b/i', $raw)) {
            return self::buildScript(self::extractScriptSrc($raw) ?? '');
        }

        $url = self::extractUrl($raw);
        if ($url !== null) {
            return self::buildIframe($url);
        }

        return '';
    }

    /**
     * Нормализация кода карты перед сохранением в настройках.
     */
    public static function normalizeForStorage(string $raw): string
    {
        $raw = self::normalizeInput($raw);
        if ($raw === '') {
            return '';
        }

        if (preg_match('/<iframe\b/i', $raw)) {
            $src = self::extractIframeSrc($raw);
            return $src !== null ? self::buildIframe($src) : '';
        }

        if (preg_match('/<script\b/i', $raw)) {
            $src = self::extractScriptSrc($raw);
            return $src !== null ? self::buildScript($src) : '';
        }

        $url = self::extractUrl($raw);
        if ($url !== null) {
            return self::buildIframe($url);
        }

        return '';
    }

    private static function normalizeInput(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        if (str_contains($raw, '&lt;')) {
            $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return trim($raw);
    }

    private static function extractUrl(string $raw): ?string
    {
        if (preg_match('#\bhttps?://[^\s<>"\']+#i', $raw, $match) !== 1) {
            return null;
        }

        $url = html_entity_decode($match[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return self::isAllowedHost($url) ? $url : null;
    }

    private static function extractIframeSrc(string $html): ?string
    {
        if (preg_match('#<iframe\b[^>]*\bsrc=["\']([^"\']+)#i', $html, $match) !== 1) {
            return null;
        }

        $src = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return self::isAllowedHost($src) ? $src : null;
    }

    private static function extractScriptSrc(string $html): ?string
    {
        if (preg_match('#<script\b[^>]*\bsrc=["\']([^"\']+)#i', $html, $match) !== 1) {
            return null;
        }

        $src = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return self::isAllowedHost($src) ? $src : null;
    }

    private static function isAllowedHost(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '') {
            return false;
        }

        return in_array($host, [
            'yandex.ru',
            'yandex.com',
            'yandex.kz',
            'yandex.by',
            'api-maps.yandex.ru',
        ], true) || str_ends_with($host, '.yandex.ru') || str_ends_with($host, '.yandex.com');
    }

    private static function buildIframe(string $src): string
    {
        if ($src === '' || !self::isAllowedHost($src)) {
            return '';
        }

        $src = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');
        $height = self::HEIGHT;

        return '<iframe src="' . $src . '" width="100%" height="' . $height . '" style="border:0;" allowfullscreen loading="lazy" title="Карта"></iframe>';
    }

    private static function buildScript(string $src): string
    {
        if ($src === '' || !self::isAllowedHost($src)) {
            return '';
        }

        $src = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');

        return '<script async src="' . $src . '" charset="utf-8"></script>';
    }
}
