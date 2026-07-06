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
            $src = self::extractIframeSrc($raw);
            if ($src !== null) {
                return self::buildIframe($src);
            }
        }

        if (preg_match('/<script\b/i', $raw)) {
            $src = self::extractScriptSrc($raw);
            if ($src !== null) {
                return self::buildScript($src);
            }
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
        if (!preg_match('#(?:https?:)?//[^\s<>"\']+#i', $raw, $match)
            && !preg_match('#\bhttps?://[^\s<>"\']+#i', $raw, $match)) {
            return null;
        }

        $url = html_entity_decode($match[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        } elseif (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        if (!self::isAllowedHost($url)) {
            return null;
        }

        return self::toEmbedUrl($url);
    }

    private static function extractIframeSrc(string $html): ?string
    {
        if (!preg_match('#\bsrc\s*=\s*(["\'])([^"\']+)\1#i', $html, $match)
            && !preg_match('#\bsrc\s*=\s*([^\s>]+)#i', $html, $match)) {
            return null;
        }

        $src = html_entity_decode($match[2] ?? $match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (str_starts_with($src, '//')) {
            $src = 'https:' . $src;
        }

        if (!self::isAllowedHost($src)) {
            return null;
        }

        return self::toEmbedUrl($src);
    }

    private static function extractScriptSrc(string $html): ?string
    {
        if (!preg_match('#\bsrc\s*=\s*(["\'])([^"\']+)\1#i', $html, $match)) {
            return null;
        }

        $src = html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (str_starts_with($src, '//')) {
            $src = 'https:' . $src;
        }

        return self::isAllowedHost($src) ? $src : null;
    }

    /**
     * Обычная ссылка yandex.ru/maps/... → формат для встраивания.
     */
    private static function toEmbedUrl(string $url): string
    {
        if (str_contains($url, 'map-widget')) {
            return $url;
        }

        if (preg_match('#https?://([^/]+\.yandex\.[^/]+)/maps/-/(\\w[\w-]*)#i', $url, $match)) {
            return 'https://' . $match[1] . '/map-widget/v1/-/' . $match[2];
        }

        return $url;
    }

    private static function isAllowedHost(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $host !== '' && str_contains($host, 'yandex.');
    }

    private static function buildIframe(string $src): string
    {
        if ($src === '' || !self::isAllowedHost($src)) {
            return '';
        }

        $src = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');
        $height = self::HEIGHT;

        return '<iframe class="yandex-map-frame" src="' . $src . '" width="100%" height="' . $height . '"'
            . ' frameborder="0" allowfullscreen="true" loading="lazy" title="Карта"></iframe>';
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
