<?php

declare(strict_types=1);

require_once __DIR__ . '/core/bootstrap.php';

use RuEdu\Engine\Auth;
use RuEdu\Engine\Config;
use RuEdu\Engine\ErrorPage;
use RuEdu\Engine\Router;
use RuEdu\Engine\Template;
use RuEdu\Engine\Hook;
use RuEdu\Engine\SEO;
use RuEdu\Engine\Cache;
use RuEdu\Engine\Migrate;
use RuEdu\Model\Page;
use RuEdu\Model\Article;
use RuEdu\Model\Menu;

if (!Config::isInstalled()) {
    if (is_dir(ROOT_PATH . '/install')) {
        Router::redirect('install/');
    }
    ErrorPage::send(503, 'CMS не установлена. Папка install/ отсутствует.');
}

Config::load();
date_default_timezone_set(Config::get('timezone', 'Europe/Moscow'));
Migrate::run();

Hook::loadModules();

$router = new Router(Router::basePath());
$template = new Template();

$render404 = function (): void {
    ErrorPage::send(404);
};

$router->setNotFoundHandler($render404);

// Sitemap
$router->get('/sitemap.xml', function () {
    header('Content-Type: application/xml; charset=utf-8');
    echo SEO::generateSitemap();
});

// Главная
$router->get('/', function () use ($template) {
    $cacheKey = 'page_home';
    $html = Auth::check() ? null : Cache::get($cacheKey);

    if ($html === null) {
        $articles = Article::getAll('published', 5);
        $menu = Menu::getByLocation('main');

        $html = $template->setData([
            'menu' => $menu,
            'articles' => $articles,
            'site_name' => Config::get('site_name'),
            'meta' => SEO::metaTags(['title' => Config::get('site_name')]),
            'schema' => SEO::schemaOrganization(),
        ])->render('home');

        if (!Auth::check()) {
            Cache::set($cacheKey, $html);
        }
    }

    echo $html;
});

// Страницы
$router->get('/page/{slug}', function (array $params) use ($template) {
    $page = Page::getBySlug($params['slug']);
    if (!$page) {
        $render404();
        return;
    }

    echo $template->setData([
        'page' => $page,
        'menu' => Menu::getByLocation('main'),
        'meta' => SEO::metaTags([
            'title' => $page['meta_title'] ?: $page['title'],
            'description' => $page['meta_description'] ?? '',
        ]),
    ])->render('page');
});

// Новости
$router->get('/news', function () use ($template) {
    $articles = Article::getAll('published');
    echo $template->setData([
        'articles' => $articles,
        'menu' => Menu::getByLocation('main'),
        'meta' => SEO::metaTags(['title' => 'Новости — ' . Config::get('site_name')]),
    ])->render('news-list');
});

$router->get('/news/{slug}', function (array $params) use ($template) {
    $article = Article::getBySlug($params['slug']);
    if (!$article) {
        $render404();
        return;
    }

    echo $template->setData([
        'article' => $article,
        'menu' => Menu::getByLocation('main'),
        'meta' => SEO::metaTags([
            'title' => $article['meta_title'] ?: $article['title'],
            'description' => $article['meta_description'] ?? $article['excerpt'] ?? '',
            'type' => 'article',
        ]),
    ])->render('news-detail');
});

Hook::registerRoutes($router);

$router->dispatch(Router::getMethod(), Router::getUri());
