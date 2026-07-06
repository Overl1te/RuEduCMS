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

function scheduleFetchClasses(Database $db): array
{
    $rows = $db->fetchAll(
        "SELECT class_name FROM (
            SELECT class_name FROM " . $db->table('schedule_classes') . "
            UNION
            SELECT DISTINCT class_name FROM " . $db->table('schedule') . "
        ) AS t"
    );

    return scheduleSortClassNames(array_column($rows, 'class_name'));
}

function scheduleSortClassNames(array $names): array
{
    $names = array_values(array_unique(array_filter(array_map('trim', $names))));
    usort($names, static function (string $a, string $b): int {
        $pa = scheduleParseClassName($a);
        $pb = scheduleParseClassName($b);
        if ($pa['grade'] !== $pb['grade']) {
            return $pa['grade'] <=> $pb['grade'];
        }
        return strcmp($pa['letter'], $pb['letter']);
    });

    return array_map(static fn (string $name) => ['class_name' => $name], $names);
}

function scheduleParseClassName(string $name): array
{
    if (preg_match('/^(\d+)\s*([А-ЯA-Zа-яa-z]?)/u', trim($name), $m)) {
        return [
            'grade' => (int) $m[1],
            'letter' => mb_strtoupper($m[2] ?? ''),
        ];
    }

    return ['grade' => PHP_INT_MAX, 'letter' => mb_strtoupper($name)];
}

function scheduleEnsureClass(Database $db, string $className): void
{
    if ($className === '') {
        return;
    }

    $db->query(
        'INSERT IGNORE INTO ' . $db->table('schedule_classes') . ' (class_name) VALUES (?)',
        [$className]
    );
}

function scheduleBuildGrid(array $rows): array
{
    $schedule = [];
    foreach ($rows as $row) {
        $schedule[$row['day_of_week']][$row['lesson_number']] = $row;
    }

    return $schedule;
}

function scheduleFetchTeachers(Database $db): array
{
    $rows = $db->fetchAll(
        "SELECT name FROM " . $db->table('staff') . " WHERE name != '' AND status = 'active' ORDER BY sort_order, name"
    );

    return array_values(array_unique(array_column($rows, 'name')));
}

function scheduleFetchSubjects(Database $db): array
{
    $rows = $db->fetchAll(
        "SELECT DISTINCT subject FROM (
            SELECT subject FROM " . $db->table('staff') . " WHERE subject != ''
            UNION
            SELECT DISTINCT subject FROM " . $db->table('schedule') . " WHERE subject != ''
        ) AS t ORDER BY subject"
    );

    return array_column($rows, 'subject');
}

Hook::on('register_routes', function ($router) use ($days) {
    $router->get('/schedule', function () use ($days) {
        $db = Database::getInstance();
        $classes = scheduleFetchClasses($db);
        $class = $_GET['class'] ?? ($classes[0]['class_name'] ?? '');

        $schedule = [];
        if ($class) {
            $rows = $db->fetchAll(
                "SELECT * FROM " . $db->table('schedule') . " WHERE class_name = ? ORDER BY day_of_week, lesson_number",
                [$class]
            );
            $schedule = scheduleBuildGrid($rows);
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
        $classes = scheduleFetchClasses($db);

        $schedule = [];
        if ($class) {
            $rows = $db->fetchAll(
                "SELECT * FROM " . $db->table('schedule') . " WHERE class_name = ? ORDER BY day_of_week, lesson_number",
                [$class]
            );
            $schedule = scheduleBuildGrid($rows);
        }

        $teachers = scheduleFetchTeachers($db);
        $subjects = scheduleFetchSubjects($db);
        modRender('index', compact('classes', 'class', 'days', 'schedule', 'teachers', 'subjects'));
    });

    $router->post('/schedule/class/save', function () {
        Auth::requireEditor();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Router::redirect('admin/schedule');
        }

        $className = trim($_POST['class_name'] ?? '');
        if ($className !== '') {
            $db = Database::getInstance();
            scheduleEnsureClass($db, $className);
            Session::flash('success', 'Класс добавлен');
            Router::redirect('admin/schedule?class=' . urlencode($className));
        }

        Router::redirect('admin/schedule');
    });

    $router->post('/schedule/save', function () {
        Auth::requireEditor();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Router::redirect('admin/schedule');
        }

        $db = Database::getInstance();
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'class_name' => trim($_POST['class_name'] ?? ''),
            'day_of_week' => (int) ($_POST['day_of_week'] ?? 1),
            'lesson_number' => (int) ($_POST['lesson_number'] ?? 1),
            'lesson_time' => trim($_POST['lesson_time'] ?? ''),
            'subject' => trim($_POST['subject'] ?? ''),
            'teacher' => trim($_POST['teacher'] ?? ''),
            'room' => trim($_POST['room'] ?? ''),
        ];

        if ($data['class_name'] !== '') {
            scheduleEnsureClass($db, $data['class_name']);
        }

        if ($id) {
            $db->update('schedule', $data, 'id = ?', [$id]);
        } else {
            if ($data['lesson_number'] < 1) {
                $max = $db->fetch(
                    'SELECT MAX(lesson_number) AS n FROM ' . $db->table('schedule')
                    . ' WHERE class_name = ? AND day_of_week = ?',
                    [$data['class_name'], $data['day_of_week']]
                );
                $data['lesson_number'] = ((int) ($max['n'] ?? 0)) + 1;
            }
            $db->insert('schedule', $data);
        }

        Session::flash('success', 'Сохранено');
        Router::redirect('admin/schedule?class=' . urlencode($data['class_name']));
    });

    $router->post('/schedule/delete/{id}', function ($p) {
        Auth::requireEditor();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Router::redirect('admin/schedule');
        }

        $db = Database::getInstance();
        $row = $db->fetch('SELECT class_name FROM ' . $db->table('schedule') . ' WHERE id = ?', [(int) $p['id']]);
        $db->delete('schedule', 'id = ?', [(int) $p['id']]);

        $redirect = 'admin/schedule';
        if ($row && !empty($row['class_name'])) {
            $redirect .= '?class=' . urlencode($row['class_name']);
        }

        Router::redirect($redirect);
    });
});

function modRender(string $t, array $d = []): void
{
    \RuEdu\Engine\AdminView::renderModule('schedule', $t, $d);
}
