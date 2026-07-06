<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class SEO
{
    public static function isIndexingAllowed(): bool
    {
        return SearchIndexer::isEnabled();
    }

    public static function metaTags(array $data = []): string
    {
        $title = $data['title'] ?? Config::get('site_name', Lang::APP_NAME);
        $description = $data['description'] ?? Config::get('site_description', '');
        if ($description === '' && !empty($data['content'])) {
            $description = self::autoDescription((string) $data['content']);
        }
        $url = $data['url'] ?? Router::currentUrl();
        $canonical = $data['canonical'] ?? $url;
        $image = $data['image'] ?? '';
        $type = $data['type'] ?? 'website';

        $tags = [
            '<title>' . htmlspecialchars($title) . '</title>',
            '<meta name="description" content="' . htmlspecialchars($description) . '">',
            '<meta name="robots" content="' . htmlspecialchars(
                $data['robots'] ?? (self::isIndexingAllowed() ? 'index, follow' : 'noindex, nofollow')
            ) . '">',
            '<meta property="og:title" content="' . htmlspecialchars($title) . '">',
            '<meta property="og:description" content="' . htmlspecialchars($description) . '">',
            '<meta property="og:url" content="' . htmlspecialchars($url) . '">',
            '<meta property="og:type" content="' . htmlspecialchars($type) . '">',
            '<meta property="og:site_name" content="' . htmlspecialchars(Config::get('site_name', '')) . '">',
            '<link rel="canonical" href="' . htmlspecialchars($canonical) . '">',
        ];

        if ($image) {
            $tags[] = '<meta property="og:image" content="' . htmlspecialchars($image) . '">';
        }

        return implode("\n    ", $tags);
    }

    public static function headLinks(): string
    {
        $links = [
            '<link rel="sitemap" type="application/xml" title="Sitemap" href="' . htmlspecialchars(Router::url('sitemap.xml')) . '">',
            '<link rel="alternate" type="application/rss+xml" title="RSS" href="' . htmlspecialchars(Router::url('news/rss')) . '">',
        ];

        return implode("\n    ", $links);
    }

    public static function autoDescription(string $html, int $maxLength = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
        if ($text === '') {
            return '';
        }

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        $truncated = mb_substr($text, 0, $maxLength);
        $lastSpace = mb_strrpos($truncated, ' ');
        if ($lastSpace !== false && $lastSpace > (int) ($maxLength * 0.6)) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return rtrim($truncated, '.,;:!?') . '…';
    }

    public static function schemaOrganization(): string
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'School',
            'name' => Config::get('site_name', ''),
            'description' => Config::get('site_description', ''),
            'url' => Config::get('site_url', ''),
            'telephone' => Config::get('contact_phone', ''),
            'email' => Config::get('admin_email', ''),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => Config::get('contact_address', ''),
            ],
        ];

        return self::jsonLd($data);
    }

    public static function schemaNewsArticle(array $article): string
    {
        $published = $article['published_at'] ?? $article['created_at'] ?? null;
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $article['meta_title'] ?: $article['title'],
            'description' => $article['meta_description'] ?: ($article['excerpt'] ?? ''),
            'url' => Router::url('news/' . $article['slug']),
            'datePublished' => $published ? date('c', strtotime((string) $published)) : null,
            'dateModified' => !empty($article['updated_at'])
                ? date('c', strtotime((string) $article['updated_at']))
                : null,
            'publisher' => [
                '@type' => 'Organization',
                'name' => Config::get('site_name', ''),
                'url' => Config::get('site_url', ''),
            ],
        ];

        if ($data['datePublished'] === null) {
            unset($data['datePublished']);
        }
        if ($data['dateModified'] === null) {
            unset($data['dateModified']);
        }

        return self::jsonLd($data);
    }

    public static function generateRobotsTxt(): string
    {
        $lines = [
            'User-agent: *',
        ];

        if (self::isIndexingAllowed()) {
            $lines[] = 'Allow: /';
        } else {
            $lines[] = 'Disallow: /';
        }

        $lines[] = 'Disallow: /admin/';
        $lines[] = 'Disallow: /install/';
        $lines[] = 'Disallow: /core/';
        $lines[] = 'Disallow: /storage/';
        $lines[] = 'Disallow: /content/modules/';
        $lines[] = '';
        $lines[] = 'Sitemap: ' . Router::url('sitemap.xml');

        return implode("\n", $lines) . "\n";
    }

    /**
     * @return list<array{title: string, items: list<array{title: string, path: string, loc: string, lastmod: ?string, priority: string}>}>
     */
    public static function getSitemapSections(): array
    {
        $db = Database::getInstance();
        $baseUrl = rtrim((string) Config::get('site_url', ''), '/');
        if ($baseUrl === '') {
            $baseUrl = rtrim(Router::url(), '/');
        }

        $makeItem = static function (
            string $title,
            string $path,
            string $priority,
            ?string $lastmod = null
        ) use ($baseUrl): array {
            $normalizedPath = $path === '/' ? '' : ltrim($path, '/');

            return [
                'title' => $title,
                'path' => $path,
                'loc' => $baseUrl . ($normalizedPath !== '' ? '/' . $normalizedPath : '/'),
                'lastmod' => $lastmod,
                'priority' => $priority,
            ];
        };

        $sections = [
            [
                'title' => 'Основные разделы',
                'items' => [
                    $makeItem('Главная', '/', '1.0'),
                    $makeItem('Сведения об образовательной организации', '/sveden', '0.9'),
                    $makeItem('Новости', '/news', '0.8'),
                    $makeItem('Информация', '/page/informaciya', '0.7'),
                    $makeItem('Проекты', '/page/proekty', '0.7'),
                    $makeItem('Фотоальбомы', '/gallery', '0.7'),
                    $makeItem('Контакты', '/contacts', '0.7'),
                    $makeItem('Расписание', '/schedule', '0.7'),
                    $makeItem('Документы', '/documents', '0.7'),
                    $makeItem('Педагогический состав', '/staff', '0.7'),
                    $makeItem('Карта сайта', '/sitemap', '0.5'),
                ],
            ],
        ];

        $pages = $db->fetchAll(
            "SELECT slug, title, updated_at FROM " . $db->table('pages') . " WHERE status = 'published' ORDER BY title"
        );
        if ($pages !== []) {
            $pageItems = [];
            foreach ($pages as $page) {
                $pageItems[] = $makeItem(
                    (string) $page['title'],
                    '/page/' . $page['slug'],
                    '0.6',
                    date('Y-m-d', strtotime((string) $page['updated_at']))
                );
            }
            $sections[] = ['title' => 'Страницы', 'items' => $pageItems];
        }

        $articles = $db->fetchAll(
            "SELECT slug, title, updated_at FROM " . $db->table('articles') . " WHERE status = 'published' ORDER BY updated_at DESC"
        );
        if ($articles !== []) {
            $articleItems = [];
            foreach ($articles as $article) {
                $articleItems[] = $makeItem(
                    (string) $article['title'],
                    '/news/' . $article['slug'],
                    '0.7',
                    date('Y-m-d', strtotime((string) $article['updated_at']))
                );
            }
            $sections[] = ['title' => 'Новости', 'items' => $articleItems];
        }

        return $sections;
    }

    public static function generateSitemap(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach (self::getSitemapSections() as $section) {
            foreach ($section['items'] as $url) {
                $xml .= "  <url>\n";
                $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
                if (!empty($url['lastmod'])) {
                    $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
                }
                $xml .= '    <priority>' . ($url['priority'] ?? '0.5') . '</priority>' . "\n";
                $xml .= "  </url>\n";
            }
        }

        $xml .= '</urlset>';
        return $xml;
    }

    public static function slugify(string $text): string
    {
        $map = [
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh',
            'з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
            'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts',
            'ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        ];

        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, $map);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function jsonLd(array $data): string
    {
        return '<script type="application/ld+json">'
            . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>';
    }
}
