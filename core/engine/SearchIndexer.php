<?php

declare(strict_types=1);

namespace RuEdu\Engine;

/**
 * Автоматическое уведомление поисковых систем (IndexNow, ping sitemap).
 */
class SearchIndexer
{
    private const INDEXNOW_API = 'https://api.indexnow.org/indexnow';
    private const KEY_FILE = 'indexnow-key.txt';

    public static function isEnabled(): bool
    {
        return (bool) Config::get('seo_indexing', true);
    }

    /**
     * Генерирует ключ IndexNow при установке или первом запуске.
     */
    public static function ensureSetup(): void
    {
        if (!Config::isInstalled()) {
            return;
        }

        $config = Config::load();
        $changed = false;

        if (!array_key_exists('seo_indexing', $config)) {
            $config['seo_indexing'] = true;
            $changed = true;
        }

        if (($config['indexnow_key'] ?? '') === '') {
            $config['indexnow_key'] = bin2hex(random_bytes(16));
            $changed = true;
        }

        if ($changed) {
            Config::save($config);
        }
    }

    public static function keyLocation(): string
    {
        return Router::url(self::KEY_FILE);
    }

    public static function key(): string
    {
        return (string) Config::get('indexnow_key', '');
    }

    /**
     * @param list<string> $paths Относительные пути: news/slug, page/slug
     */
    public static function notifyPaths(array $paths): void
    {
        if (!self::isEnabled()) {
            return;
        }

        $urls = array_map(static fn (string $path): string => Router::url($path), $paths);
        self::notifyUrls($urls);
    }

    /**
     * @param list<string> $urls Абсолютные URL
     */
    public static function notifyUrls(array $urls): void
    {
        if (!self::isEnabled() || $urls === []) {
            return;
        }

        self::ensureSetup();

        $key = self::key();
        if ($key === '') {
            return;
        }

        $host = parse_url(Router::url(), PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return;
        }

        self::postJson(self::INDEXNOW_API, [
            'host' => $host,
            'key' => $key,
            'keyLocation' => self::keyLocation(),
            'urlList' => array_values(array_unique($urls)),
        ]);

        self::pingSitemap();
    }

    public static function onContentPublished(string $type, array $data): void
    {
        if (($data['status'] ?? '') !== 'published') {
            return;
        }

        $slug = (string) ($data['slug'] ?? '');
        if ($slug === '') {
            return;
        }

        $path = match ($type) {
            'article' => 'news/' . $slug,
            'page' => 'page/' . $slug,
            default => '',
        };

        if ($path === '') {
            return;
        }

        self::notifyPaths([$path]);
    }

    public static function serveKeyFile(): void
    {
        $key = self::key();
        if ($key === '') {
            ErrorPage::send(404);
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=86400');
        echo $key;
    }

    private static function pingSitemap(): void
    {
        $sitemapUrl = Router::url('sitemap.xml');
        $encoded = rawurlencode($sitemapUrl);

        self::get('https://www.bing.com/ping?sitemap=' . $encoded);
    }

    private static function postJson(string $url, array $body): void
    {
        if (!function_exists('curl_init')) {
            return;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private static function get(string $url): void
    {
        if (!function_exists('curl_init')) {
            return;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
