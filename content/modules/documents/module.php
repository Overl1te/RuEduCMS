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
    $router->get('/documents', function () {
        $db = Database::getInstance();
        $docs = $db->fetchAll("SELECT * FROM " . $db->table('documents') . " ORDER BY category, sort_order, title");
        $grouped = [];
        foreach ($docs as $d) {
            $grouped[$d['category']][] = $d;
        }
        $template = new Template();
        echo $template->setData([
            'documents' => $grouped,
            'menu' => Menu::getByLocation('main'),
            'meta' => SEO::metaTags(['title' => 'Документы — ' . Config::get('site_name')]),
        ])->render('documents');
    });
});

Hook::on('admin_menu', function ($menu) {
    $menu[] = ['title' => 'Документы', 'url' => Router::path('admin/documents'), 'icon' => 'bi-file-earmark-pdf'];
    return $menu;
});

Hook::on('register_admin_routes', function ($router) {
    $router->get('/documents', function () {
        Auth::requireEditor();
        $db = Database::getInstance();
        $docs = $db->fetchAll("SELECT * FROM " . $db->table('documents') . " ORDER BY category, title");
        docRender('index', compact('docs'));
    });

    $router->post('/documents/save', function () {
        Auth::requireEditor();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) Router::redirect('admin/documents');
        $db = Database::getInstance();
        $filePath = $_POST['existing_file'] ?? '';

        if (!empty($_FILES['file']['name'])) {
            $upload = Media::upload($_FILES['file'], Auth::id());
            if ($upload) $filePath = $upload['path'];
        }

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'category' => trim($_POST['category'] ?? 'general'),
            'file_path' => $filePath,
            'published_at' => $_POST['published_at'] ?: null,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        $id = (int)($_POST['id'] ?? 0);
        if ($id) $db->update('documents', $data, 'id = ?', [$id]);
        else { $data['created_at'] = date('Y-m-d H:i:s'); $db->insert('documents', $data); }

        Session::flash('success', 'Документ сохранён');
        Router::redirect('admin/documents');
    });

    $router->post('/documents/delete/{id}', function ($p) {
        Auth::requireEditor();
        Database::getInstance()->delete('documents', 'id = ?', [(int)$p['id']]);
        Router::redirect('admin/documents');
    });
});

function docRender(string $t, array $d = []): void {
    \RuEdu\Engine\AdminView::renderModule('documents', $t, $d);
}
