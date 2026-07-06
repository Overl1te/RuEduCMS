<?php

use RuEdu\Engine\Hook;
use RuEdu\Engine\Database;
use RuEdu\Engine\Auth;
use RuEdu\Engine\ErrorPage;
use RuEdu\Engine\Session;
use RuEdu\Engine\Router;
use RuEdu\Engine\Config;
use RuEdu\Model\Setting;

Hook::on('register_routes', function ($router) {
    $router->post('/forms/submit/{slug}', function ($params) {
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        $respond = function (bool $ok, string $message, int $status = 200) use ($isAjax): void {
            if ($isAjax) {
                http_response_code($status);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
                exit;
            }
            Session::flash($ok ? 'site_success' : 'site_error', $message);
            Router::redirect('contacts');
        };

        $db = Database::getInstance();
        $form = $db->fetch("SELECT * FROM " . $db->table('forms') . " WHERE slug = ? AND status = 'active'", [$params['slug']]);

        if (!$form) {
            if ($isAjax) {
                $respond(false, 'Форма не найдена', 404);
            }
            ErrorPage::send(404);
        }

        if (empty($_POST['consent'])) {
            $respond(false, 'Необходимо согласие на обработку персональных данных', 422);
        }

        $submissionData = [];
        foreach ($_POST as $key => $value) {
            if ($key !== 'consent') {
                $submissionData[$key] = trim((string) $value);
            }
        }

        if ($submissionData === [] || in_array('', $submissionData, true)) {
            $respond(false, 'Заполните все поля формы', 422);
        }

        $db->insert('form_submissions', [
            'form_id' => $form['id'],
            'data' => json_encode($submissionData, JSON_UNESCAPED_UNICODE),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $emailTo = $form['email_to'] ?: Config::get('admin_email', '');
        if ($emailTo) {
            $subject = 'Новая заявка: ' . $form['name'];
            $body = "Получена новая заявка с формы \"{$form['name']}\":\n\n";
            foreach ($submissionData as $k => $v) {
                $body .= "{$k}: {$v}\n";
            }
            @mail($emailTo, $subject, $body, 'From: ' . $emailTo);
        }

        $respond(true, 'Ваше сообщение отправлено!');
    });
});

Hook::on('register_admin_routes', function ($router) {
    $router->get('/forms/submissions', function () {
        Auth::requireAuth();
        $db = Database::getInstance();
        $submissions = $db->fetchAll(
            "SELECT fs.*, f.name as form_name FROM " . $db->table('form_submissions') . " fs
             LEFT JOIN " . $db->table('forms') . " f ON fs.form_id = f.id
             ORDER BY fs.created_at DESC LIMIT 100"
        );
        formsRender('submissions', compact('submissions'));
    });

    $router->post('/forms/submissions/read/{id}', function ($p) {
        Auth::requireAuth();
        Database::getInstance()->update('form_submissions', ['is_read' => 1], 'id = ?', [(int)$p['id']]);
        Router::redirect('admin/forms/submissions');
    });

    $router->get('/forms', function () {
        Auth::requireEditor();
        $db = Database::getInstance();
        $forms = $db->fetchAll("SELECT * FROM " . $db->table('forms') . " ORDER BY name");
        formsRender('index', compact('forms'));
    });

    $router->post('/forms/save', function () {
        Auth::requireEditor();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) Router::redirect('admin/forms');
        $db = Database::getInstance();
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'slug' => \RuEdu\Engine\SEO::slugify($_POST['name'] ?? ''),
            'fields' => json_encode([['name' => 'name', 'label' => 'Имя', 'type' => 'text'], ['name' => 'email', 'label' => 'Email', 'type' => 'email'], ['name' => 'message', 'label' => 'Сообщение', 'type' => 'textarea']]),
            'email_to' => trim($_POST['email_to'] ?? ''),
            'status' => 'active',
        ];
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $db->update('forms', $data, 'id = ?', [$id]);
        else { $data['created_at'] = date('Y-m-d H:i:s'); $db->insert('forms', $data); }
        Session::flash('success', 'Форма сохранена');
        Router::redirect('admin/forms');
    });
});

// Создать форму контактов по умолчанию при первой загрузке
Hook::on('register_routes', function () {
    try {
        $db = Database::getInstance();
        $exists = $db->fetch("SELECT id FROM " . $db->table('forms') . " WHERE slug = 'contact'");
        if (!$exists) {
            $db->insert('forms', [
                'name' => 'Обратная связь',
                'slug' => 'contact',
                'fields' => json_encode([]),
                'email_to' => Config::get('admin_email', ''),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    } catch (\Exception $e) {
        // DB not ready yet
    }
});

function formsRender(string $t, array $d = []): void {
    \RuEdu\Engine\AdminView::renderModule('forms', $t, $d);
}
