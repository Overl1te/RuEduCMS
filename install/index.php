<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/core/bootstrap.php';

use RuEdu\Engine\Config;
use RuEdu\Engine\Database;
use RuEdu\Engine\Auth;
use RuEdu\Engine\Router;
use RuEdu\Engine\Migrate;
use RuEdu\Model\User;

if (Config::isInstalled()) {
    Router::redirect('');
}

$step = (int) ($_GET['step'] ?? 1);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 2:
            $host = trim($_POST['db_host'] ?? 'localhost');
            $name = trim($_POST['db_name'] ?? '');
            $user = trim($_POST['db_user'] ?? '');
            $pass = $_POST['db_pass'] ?? '';
            $prefix = trim($_POST['db_prefix'] ?? 'rc_');

            try {
                $pdo = Database::createConnection($host, $name, $user, $pass);
                $_SESSION['install_db'] = compact('host', 'name', 'user', 'pass', 'prefix');
                header('Location: ?step=3');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Ошибка подключения к БД: ' . $e->getMessage();
            }
            break;

        case 3:
            if (empty($_SESSION['install_db'])) {
                header('Location: ?step=2');
                exit;
            }
            $dbConfig = $_SESSION['install_db'];
            try {
                $pdo = Database::createConnection($dbConfig['host'], $dbConfig['name'], $dbConfig['user'], $dbConfig['pass']);
                $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
                $sql = str_replace('{{prefix}}', $dbConfig['prefix'], $sql);
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                    if ($stmt !== '') {
                        $pdo->exec($stmt);
                    }
                }
                header('Location: ?step=4');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Ошибка создания таблиц: ' . $e->getMessage();
            }
            break;

        case 4:
            if (empty($_SESSION['install_db'])) {
                header('Location: ?step=2');
                exit;
            }
            $dbConfig = $_SESSION['install_db'];
            $siteName = trim($_POST['site_name'] ?? 'Мой сайт');
            $siteUrl = rtrim(trim($_POST['site_url'] ?? ''), '/');
            if ($siteUrl === '') {
                $siteUrl = Router::detectSiteUrl();
            }
            $basePath = Router::basePath();
            $adminName = trim($_POST['admin_name'] ?? '');
            $adminLogin = Migrate::normalizeLogin(trim($_POST['admin_login'] ?? ''));
            $adminEmail = trim($_POST['admin_email'] ?? '');
            $adminPass = $_POST['admin_password'] ?? '';

            if ($adminLogin === '') {
                $errors[] = 'Укажите корректный логин';
            }

            if (strlen($adminPass) < 6) {
                $errors[] = 'Пароль должен быть не менее 6 символов';
            }
            if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Некорректный email';
            }

            if (empty($errors)) {
                try {
                    $config = [
                        'db_host' => $dbConfig['host'],
                        'db_name' => $dbConfig['name'],
                        'db_user' => $dbConfig['user'],
                        'db_pass' => $dbConfig['pass'],
                        'db_prefix' => $dbConfig['prefix'],
                        'site_url' => $siteUrl,
                        'base_path' => $basePath,
                        'site_name' => $siteName,
                        'site_description' => 'Сайт образовательного учреждения',
                        'admin_email' => $adminEmail,
                        'timezone' => 'Europe/Moscow',
                        'theme' => 'default-school',
                        'language' => 'ru',
                        'debug' => false,
                        'installed' => true,
                        'secret_key' => bin2hex(random_bytes(32)),
                        'cache_enabled' => true,
                        'scss_runtime' => false,
                        'db_version' => RUEDU_VERSION,
                        'update_source' => null,
                    ];

                    Config::save($config);

                    $pdo = Database::createConnection($dbConfig['host'], $dbConfig['name'], $dbConfig['user'], $dbConfig['pass']);
                    $prefix = $dbConfig['prefix'];
                    $now = date('Y-m-d H:i:s');

                    $stmt = $pdo->prepare("INSERT INTO {$prefix}users (name, login, email, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'admin', 'active', ?, ?)");
                    $stmt->execute([$adminName, $adminLogin, $adminEmail, Auth::hashPassword($adminPass), $now, $now]);

                    unset($_SESSION['install_db']);

                    register_shutdown_function(function (): void {
                        ruedu_delete_directory(ROOT_PATH . '/install');
                    });

                    Router::redirect('');
                } catch (Exception $e) {
                    $errors[] = 'Ошибка завершения установки: ' . $e->getMessage();
                }
            }
            break;
    }
}

function checkRequirements(): array
{
    $checks = [];
    $checks['php_version'] = [
        'label' => 'PHP версии 8.2 и выше',
        'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
        'value' => PHP_VERSION,
    ];
    $extensionLabels = [
        'pdo_mysql' => 'Расширение PDO MySQL',
        'mbstring' => 'Расширение многобайтовых строк',
        'json' => 'Расширение JSON',
        'gd' => 'Расширение GD (изображения)',
    ];
    foreach (['pdo_mysql', 'mbstring', 'json', 'gd'] as $ext) {
        $checks[$ext] = [
            'label' => $extensionLabels[$ext],
            'ok' => extension_loaded($ext),
            'value' => extension_loaded($ext) ? 'Установлено' : 'Не найдено',
        ];
    }
    $checks['config_writable'] = [
        'label' => 'Корневая папка доступна для записи (config.php)',
        'ok' => is_writable(ROOT_PATH),
        'value' => is_writable(ROOT_PATH) ? 'Да' : 'Нет',
    ];
    $checks['uploads_writable'] = [
        'label' => 'Папка загрузок доступна для записи',
        'ok' => is_writable(UPLOADS_PATH),
        'value' => is_writable(UPLOADS_PATH) ? 'Да' : 'Нет',
    ];
    $checks['zip'] = [
        'label' => 'Расширение ZIP (обновления)',
        'ok' => class_exists('ZipArchive'),
        'value' => class_exists('ZipArchive') ? 'Установлено' : 'Не найдено',
    ];
    return $checks;
}

$requirements = checkRequirements();
$allOk = !in_array(false, array_column($requirements, 'ok'), true);

include __DIR__ . '/templates/layout.php';
