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

$days = [1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг', 5 => 'Пятница', 6 => 'Суббота'];

Hook::on('register_routes', function ($router) use ($days) {
    $router->get('/schedule', function () use ($days) {
        $db = Database::getInstance();
        $classes = $db->fetchAll("SELECT DISTINCT class_name FROM " . $db->table('schedule') . " ORDER BY class_name");
        $class = $_GET['class'] ?? ($classes[0]['class_name'] ?? '');

        $schedule = [];
        if ($class) {
            $rows = $db->fetchAll(
                "SELECT * FROM " . $db->table('schedule') . " WHERE class_name = ? ORDER BY day_of_week, lesson_number",
                [$class]
            );
            foreach ($rows as $row) {
                $schedule[$row['day_of_week']][$row['lesson_number']] = $row;
            }
        }

        $template = new Template();
        echo $template->setData([
            'schedule' => $schedule,
            'classes' => $classes,
            'currentClass' => $class,
            'days' => $days,
            'menu' => Menu::getByLocation('main'),
            'meta' => SEO::metaTags(['title' => 'Расписание — ' . Config::get('site_name')]),
        ])->render('schedule');
    });
});

Hook::on('admin_menu', function ($menu) {
    $menu[] = ['title' => 'Расписание', 'url' => Router::path('admin/schedule'), 'icon' => 'bi-calendar3'];
    return $menu;
});

Hook::on('register_admin_routes', function ($router) use ($days) {
    $router->get('/schedule', function () use ($days) {
        Auth::requireEditor();
        $db = Database::getInstance();
        $class = $_GET['class'] ?? '';
        $sql = "SELECT * FROM " . $db->table('schedule');
        $params = [];
        if ($class) { $sql .= " WHERE class_name = ?"; $params[] = $class; }
        $sql .= " ORDER BY class_name, day_of_week, lesson_number";
        $items = $db->fetchAll($sql, $params);
        $classes = $db->fetchAll("SELECT DISTINCT class_name FROM " . $db->table('schedule') . " ORDER BY class_name");
        modRender('index', compact('items', 'classes', 'class', 'days'));
    });

    $router->post('/schedule/save', function () {
        Auth::requireEditor();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) Router::redirect('admin/schedule');
        $db = Database::getInstance();
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'class_name' => trim($_POST['class_name'] ?? ''),
            'day_of_week' => (int)($_POST['day_of_week'] ?? 1),
            'lesson_number' => (int)($_POST['lesson_number'] ?? 1),
            'subject' => trim($_POST['subject'] ?? ''),
            'teacher' => trim($_POST['teacher'] ?? ''),
            'room' => trim($_POST['room'] ?? ''),
        ];
        if ($id) $db->update('schedule', $data, 'id = ?', [$id]);
        else $db->insert('schedule', $data);
        Session::flash('success', 'Сохранено');
        Router::redirect('admin/schedule?class=' . urlencode($data['class_name']));
    });

    $router->post('/schedule/delete/{id}', function ($p) {
        Auth::requireEditor();
        Database::getInstance()->delete('schedule', 'id = ?', [(int)$p['id']]);
        Router::redirect('admin/schedule');
    });
});

function modRender(string $t, array $d = []): void {
    \RuEdu\Engine\AdminView::renderModule('schedule', $t, $d);
}
