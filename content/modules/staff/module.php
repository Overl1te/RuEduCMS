<?php

use RuEdu\Engine\Hook;
use RuEdu\Engine\Database;
use RuEdu\Engine\Template;
use RuEdu\Engine\SEO;
use RuEdu\Engine\Config;
use RuEdu\Engine\Auth;
use RuEdu\Engine\Session;
use RuEdu\Engine\Router;
use RuEdu\Model\Menu;

Hook::on('register_routes', function ($router) {
    $router->get('/staff', function () {
        $db = Database::getInstance();
        $staff = $db->fetchAll("SELECT * FROM " . $db->table('staff') . " WHERE status = 'active' ORDER BY sort_order, name");
        $view = $_GET['view'] ?? 'cards';

        $template = new Template();
        echo $template->setData([
            'staff' => $staff,
            'view' => $view,
            'menu' => Menu::getByLocation('main'),
            'meta' => SEO::metaTags(['title' => 'Педагогический состав — ' . Config::get('site_name')]),
        ])->render('staff');
    });
});

Hook::on('admin_menu', function ($menu) {
    $menu[] = ['title' => 'Педагоги', 'url' => Router::path('admin/staff'), 'icon' => 'bi-person-badge'];
    return $menu;
});

Hook::on('register_admin_routes', function ($router) {
    $router->get('/staff', function () {
        Auth::requireEditor();
        $db = Database::getInstance();
        $staff = $db->fetchAll("SELECT * FROM " . $db->table('staff') . " ORDER BY sort_order, name");
        staffRenderAdmin('index', compact('staff'));
    });

    $router->get('/staff/create', function () {
        Auth::requireEditor();
        staffRenderAdmin('form', ['member' => null]);
    });

    $router->get('/staff/edit/{id}', function ($p) {
        Auth::requireEditor();
        $db = Database::getInstance();
        $member = $db->fetch("SELECT * FROM " . $db->table('staff') . " WHERE id = ?", [(int)$p['id']]);
        staffRenderAdmin('form', compact('member'));
    });

    $router->post('/staff/save', function () {
        Auth::requireEditor();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) Router::redirect('admin/staff');

        $db = Database::getInstance();
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'subject' => trim($_POST['subject'] ?? ''),
            'education' => trim($_POST['education'] ?? ''),
            'qualification' => trim($_POST['qualification'] ?? ''),
            'experience' => trim($_POST['experience'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'status' => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->update('staff', $data, 'id = ?', [$id]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->insert('staff', $data);
        }

        Session::flash('success', 'Сотрудник сохранён');
        Router::redirect('admin/staff');
    });

    $router->post('/staff/delete/{id}', function ($p) {
        Auth::requireEditor();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Router::redirect('admin/staff');
        }
        Database::getInstance()->delete('staff', 'id = ?', [(int)$p['id']]);
        Session::flash('success', 'Удалено');
        Router::redirect('admin/staff');
    });
});

function staffRenderAdmin(string $template, array $data = []): void
{
    \RuEdu\Engine\AdminView::renderModule('staff', $template, $data);
}
