<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class SEO
{
    public static function metaTags(array $data = []): string
    {
        $title = $data['title'] ?? Config::get('site_name', Lang::APP_NAME);
        $description = $data['description'] ?? Config::get('site_description', '');
        $url = $data['url'] ?? Router::url();
        $image = $data['image'] ?? '';
        $type = $data['type'] ?? 'website';

        $tags = [
            '<title>' . htmlspecialchars($title) . '</title>',
            '<meta name="description" content="' . htmlspecialchars($description) . '">',
            '<meta property="og:title" content="' . htmlspecialchars($title) . '">',
            '<meta property="og:description" content="' . htmlspecialchars($description) . '">',
            '<meta property="og:url" content="' . htmlspecialchars($url) . '">',
            '<meta property="og:type" content="' . htmlspecialchars($type) . '">',
            '<meta property="og:site_name" content="' . htmlspecialchars(Config::get('site_name', '')) . '">',
        ];

        if ($image) {
            $tags[] = '<meta property="og:image" content="' . htmlspecialchars($image) . '">';
        }

        if (!empty($data['canonical'])) {
            $tags[] = '<link rel="canonical" href="' . htmlspecialchars($data['canonical']) . '">';
        }

        return implode("\n    ", $tags);
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

        return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
    }

    public static function generateSitemap(): string
    {
        $db = Database::getInstance();
        $baseUrl = rtrim((string) Config::get('site_url', ''), '/');

        $urls = [
            ['loc' => $baseUrl . '/', 'priority' => '1.0'],
            ['loc' => $baseUrl . '/news', 'priority' => '0.8'],
            ['loc' => $baseUrl . '/sveden', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/contacts', 'priority' => '0.7'],
        ];

        $pages = $db->fetchAll(
            "SELECT slug, updated_at FROM " . $db->table('pages') . " WHERE status = 'published'"
        );
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => $baseUrl . '/page/' . $page['slug'],
                'lastmod' => date('Y-m-d', strtotime($page['updated_at'])),
                'priority' => '0.6',
            ];
        }

        $articles = $db->fetchAll(
            "SELECT slug, updated_at FROM " . $db->table('articles') . " WHERE status = 'published'"
        );
        foreach ($articles as $article) {
            $urls[] = [
                'loc' => $baseUrl . '/news/' . $article['slug'],
                'lastmod' => date('Y-m-d', strtotime($article['updated_at'])),
                'priority' => '0.7',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
            if (!empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            }
            $xml .= '    <priority>' . ($url['priority'] ?? '0.5') . '</priority>' . "\n";
            $xml .= "  </url>\n";
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
}
