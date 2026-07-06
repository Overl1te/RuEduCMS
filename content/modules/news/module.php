<?php

use RuEdu\Engine\Hook;
use RuEdu\Engine\Config;
use RuEdu\Engine\Router;
use RuEdu\Model\Article;

Hook::on('register_routes', function ($router) {
    $router->get('/news/rss', function () {
        header('Content-Type: application/rss+xml; charset=utf-8');
        $articles = Article::getAll('published', 20);
        $siteUrl = Router::url();
        $siteName = Config::get('site_name', '');

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<rss version="2.0"><channel>';
        echo '<title>' . htmlspecialchars($siteName) . '</title>';
        echo '<link>' . htmlspecialchars($siteUrl) . '</link>';
        echo '<description>' . htmlspecialchars(Config::get('site_description', '')) . '</description>';

        foreach ($articles as $a) {
            echo '<item>';
            echo '<title>' . htmlspecialchars($a['title']) . '</title>';
            echo '<link>' . htmlspecialchars(Router::url('news/' . $a['slug'])) . '</link>';
            echo '<guid>' . htmlspecialchars(Router::url('news/' . $a['slug'])) . '</guid>';
            echo '<pubDate>' . date('r', strtotime($a['published_at'] ?? $a['created_at'])) . '</pubDate>';
            echo '<description>' . htmlspecialchars($a['excerpt'] ?? '') . '</description>';
            echo '</item>';
        }

        echo '</channel></rss>';
    });
});
