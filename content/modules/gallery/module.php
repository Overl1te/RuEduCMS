<?php

use RuEdu\Engine\Hook;
use RuEdu\Engine\Database;
use RuEdu\Engine\Template;
use RuEdu\Engine\SEO;
use RuEdu\Engine\Config;
use RuEdu\Engine\Auth;
use RuEdu\Engine\Session;
use RuEdu\Engine\Router;
use RuEdu\Engine\Media;
use RuEdu\Model\Menu;

Hook::on('register_routes', function ($router) {
    $router->get('/gallery', function () {
        $db = Database::getInstance();
        $albums = $db->fetchAll("SELECT * FROM " . $db->table('gallery_albums') . " ORDER BY sort_order, title");
        $template = new Template();
        echo $template->setData([
            'albums' => $albums,
            'menu' => Menu::getByLocation('main'),
            'meta' => SEO::metaTags(['title' => 'Галерея — ' . Config::get('site_name')]),
        ])->render('gallery');
    });

    $router->get('/gallery/{slug}', function ($params) {
        $db = Database::getInstance();
        $album = $db->fetch("SELECT * FROM " . $db->table('gallery_albums') . " WHERE slug = ?", [$params['slug']]);
        if (!$album) { http_response_code(404); return; }
        $images = $db->fetchAll("SELECT * FROM " . $db->table('gallery_images') . " WHERE album_id = ? ORDER BY sort_order", [$album['id']]);
        $template = new Template();
        echo $template->setData([
            'album' => $album,
            'images' => $images,
            'menu' => Menu::getByLocation('main'),
            'meta' => SEO::metaTags(['title' => $album['title'] . ' — ' . Config::get('site_name')]),
        ])->render('gallery-album');
    });
});

Hook::on('admin_menu', function ($menu) {
    $menu[] = ['title' => 'Галерея', 'url' => Router::path('admin/gallery'), 'icon' => 'bi-camera'];
    return $menu;
});

Hook::on('register_admin_routes', function ($router) {
    $router->get('/gallery', function () {
        Auth::requireEditor();
        $db = Database::getInstance();
        $albums = $db->fetchAll("SELECT a.*, (SELECT COUNT(*) FROM " . $db->table('gallery_images') . " gi WHERE gi.album_id = a.id) as image_count FROM " . $db->table('gallery_albums') . " a ORDER BY sort_order");
        galRender('index', compact('albums'));
    });

    $router->post('/gallery/album/save', function () {
        Auth::requireEditor();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) Router::redirect('admin/gallery');
        $db = Database::getInstance();
        $slug = \RuEdu\Engine\SEO::slugify($_POST['title'] ?? '');
        $data = ['title' => trim($_POST['title'] ?? ''), 'slug' => $slug, 'description' => trim($_POST['description'] ?? '')];
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $db->update('gallery_albums', $data, 'id = ?', [$id]);
        else { $data['created_at'] = date('Y-m-d H:i:s'); $db->insert('gallery_albums', $data); }
        Session::flash('success', 'Альбом сохранён');
        Router::redirect('admin/gallery');
    });

    $router->post('/gallery/upload/{albumId}', function ($p) {
        Auth::requireEditor();
        $albumId = (int)$p['albumId'];
        if (!empty($_FILES['images'])) {
            $db = Database::getInstance();
            foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = ['name' => $_FILES['images']['name'][$i], 'type' => $_FILES['images']['type'][$i], 'tmp_name' => $tmp, 'error' => 0, 'size' => $_FILES['images']['size'][$i]];
                    $upload = Media::upload($file, Auth::id());
                    if ($upload) {
                        $db->insert('gallery_images', ['album_id' => $albumId, 'path' => $upload['path'], 'title' => $upload['filename'], 'sort_order' => 0]);
                    }
                }
            }
        }
        Session::flash('success', 'Изображения загружены');
        Router::redirect('admin/gallery');
    });
});

function galRender(string $t, array $d = []): void {
    \RuEdu\Engine\AdminView::renderModule('gallery', $t, $d);
}
