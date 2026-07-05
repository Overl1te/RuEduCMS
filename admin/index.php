<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/core/bootstrap.php';

use RuEdu\Engine\Config;
use RuEdu\Engine\Router;
use RuEdu\Engine\Auth;
use RuEdu\Engine\Session;
use RuEdu\Engine\Hook;
use RuEdu\Engine\Media;
use RuEdu\Engine\Cache;
use RuEdu\Engine\Updater;
use RuEdu\Engine\Migrate;
use RuEdu\Engine\Version;
use RuEdu\Engine\ThemeEditor;
use RuEdu\Engine\SetupRecommendations;
use RuEdu\Engine\SystemPages;
use RuEdu\Model\Page;
use RuEdu\Model\Article;
use RuEdu\Model\User;
use RuEdu\Model\Menu;
use RuEdu\Model\Setting;
use RuEdu\Controller\AdminBase;

if (!Config::isInstalled()) {
    if (is_dir(ROOT_PATH . '/install')) {
        Router::redirect('install/');
    }
    http_response_code(503);
    echo 'CMS не установлена.';
    exit;
}

Config::load();
Migrate::run();
Hook::loadModules();

$router = new Router(Router::basePath() . '/admin');
$admin = new class extends AdminBase {
    public function render(string $t, array $d = []): void { $this->view($t, $d); }
};

// Авторизация
$router->get('/login', function () use ($admin) {
    if (Auth::check()) {
        Router::redirect('admin/');
    }
    $admin->render('auth/login');
});

$router->post('/login', function () {
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Session::flash('error', 'Ошибка безопасности. Обновите страницу и попробуйте снова.');
        Router::redirect('admin/login');
    }

    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    // Rate limiting
    $db = \RuEdu\Engine\Database::getInstance();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $attempts = $db->count('login_attempts', 'ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)', [$ip]);

    if ($attempts >= 5) {
        Session::flash('error', 'Слишком много попыток. Подождите 15 минут.');
        Router::redirect('admin/login');
    }

    if (Auth::attempt($login, $password)) {
        $db->delete('login_attempts', 'ip_address = ?', [$ip]);
        Router::redirect('admin/');
    }

    $db->insert('login_attempts', [
        'ip_address' => $ip,
        'email' => $login,
        'attempted_at' => date('Y-m-d H:i:s'),
    ]);

    Session::flash('error', 'Неверный логин или пароль');
    Router::redirect('admin/login');
});

$router->get('/forgot-password', function () use ($admin) {
    if (Auth::check()) {
        Router::redirect('admin/');
    }
    $admin->render('auth/forgot-password');
});

$router->post('/forgot-password', function () {
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Session::flash('error', 'Ошибка безопасности. Обновите страницу и попробуйте снова.');
        Router::redirect('admin/forgot-password');
    }

    $loginOrEmail = trim($_POST['login'] ?? '');
    $db = \RuEdu\Engine\Database::getInstance();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $attempts = $db->count('login_attempts', 'ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)', [$ip]);

    if ($attempts >= 10) {
        Session::flash('error', 'Слишком много запросов. Подождите 15 минут.');
        Router::redirect('admin/forgot-password');
    }

    if ($loginOrEmail === '') {
        Session::flash('error', 'Укажите логин или email');
        Router::redirect('admin/forgot-password');
    }

    $db->insert('login_attempts', [
        'ip_address' => $ip,
        'email' => $loginOrEmail,
        'attempted_at' => date('Y-m-d H:i:s'),
    ]);

    $sent = Auth::sendPasswordReset($loginOrEmail);
    if ($sent) {
        Session::flash('success', 'Если аккаунт существует, на email отправлена ссылка для сброса пароля.');
    } else {
        Session::flash('error', 'Не удалось отправить письмо. Проверьте email администратора в настройках.');
    }

    Router::redirect('admin/forgot-password');
});

$router->get('/reset-password', function () use ($admin) {
    if (Auth::check()) {
        Router::redirect('admin/');
    }

    $token = trim($_GET['token'] ?? '');
    $reset = Auth::findPasswordReset($token);
    if (!$reset) {
        Session::flash('error', 'Ссылка недействительна или истекла.');
        Router::redirect('admin/forgot-password');
    }

    $admin->render('auth/reset-password', ['token' => $token]);
});

$router->post('/reset-password', function () {
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Session::flash('error', 'Ошибка безопасности. Обновите страницу и попробуйте снова.');
        Router::redirect('admin/forgot-password');
    }

    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (strlen($password) < 6) {
        Session::flash('error', 'Пароль должен быть не менее 6 символов');
        Router::redirect('admin/reset-password?token=' . urlencode($token));
    }

    if ($password !== $passwordConfirm) {
        Session::flash('error', 'Пароли не совпадают');
        Router::redirect('admin/reset-password?token=' . urlencode($token));
    }

    if (!Auth::resetPassword($token, $password)) {
        Session::flash('error', 'Ссылка недействительна или истекла.');
        Router::redirect('admin/forgot-password');
    }

    Session::flash('success', 'Пароль успешно изменён. Войдите с новым паролем.');
    Router::redirect('admin/login');
});

$router->get('/logout', function () {
    Auth::logout();
    Router::redirect('admin/login');
});

// Dashboard
$router->get('/', function () use ($admin) {
    Auth::requireAuth();
    $db = \RuEdu\Engine\Database::getInstance();
    $stats = [
        'pages' => $db->count('pages'),
        'articles' => $db->count('articles'),
        'users' => $db->count('users'),
        'media' => $db->count('media'),
        'forms_unread' => $db->count('form_submissions', 'is_read = 0'),
    ];
    $update = Updater::checkForUpdate();
    $needsDbUpdate = Version::needsDbUpdate();
    $recommendations = SetupRecommendations::getAll();
    $admin->render('dashboard/index', compact('stats', 'update', 'needsDbUpdate', 'recommendations'));
});

$router->get('', function () use ($admin) {
    Auth::requireAuth();
    Router::redirect('admin/');
});

// Pages CRUD
$router->get('/pages', function () use ($admin) {
    Auth::requireAuth();
    $pages = Page::getAll();
    $systemPages = SystemPages::getAll();
    $activeTheme = Config::get('theme', 'default-school');
    $admin->render('pages/index', compact('pages', 'systemPages', 'activeTheme'));
});

$router->get('/pages/create', function () use ($admin) {
    Auth::requireAuth();
    $admin->render('pages/form', ['page' => null]);
});

$router->get('/pages/edit/{id}', function (array $p) use ($admin) {
    Auth::requireAuth();
    $page = Page::getById((int) $p['id']);
    if (!$page) { Router::redirect('admin/pages'); }
    $admin->render('pages/form', compact('page'));
});

$router->post('/pages/save', function () {
    Auth::requireAuth();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) { Router::redirect('admin/pages'); }

    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'status' => $_POST['status'] ?? 'draft',
        'author_id' => Auth::id(),
    ];

    if (!Auth::canPublish() && $data['status'] === 'published') {
        $data['status'] = 'draft';
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id) {
        Page::update($id, $data);
    } else {
        Page::create($data);
    }

    Cache::flush();
    Session::flash('success', 'Страница сохранена');
    Router::redirect('admin/pages');
});

$router->post('/pages/delete/{id}', function (array $p) {
    Auth::requireEditor();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) { Router::redirect('admin/pages'); }
    Page::delete((int) $p['id']);
    Cache::flush();
    Session::flash('success', 'Страница удалена');
    Router::redirect('admin/pages');
});

// Articles CRUD
$router->get('/articles', function () use ($admin) {
    Auth::requireAuth();
    $articles = Article::getAll();
    $admin->render('articles/index', compact('articles'));
});

$router->get('/articles/create', function () use ($admin) {
    Auth::requireAuth();
    $categories = Article::getCategories();
    $admin->render('articles/form', ['article' => null, 'categories' => $categories]);
});

$router->get('/articles/edit/{id}', function (array $p) use ($admin) {
    Auth::requireAuth();
    $article = Article::getById((int) $p['id']);
    if (!$article) { Router::redirect('admin/articles'); }
    $categories = Article::getCategories();
    $admin->render('articles/form', compact('article', 'categories'));
});

$router->post('/articles/save', function () {
    Auth::requireAuth();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) { Router::redirect('admin/articles'); }

    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'category_id' => (int) ($_POST['category_id'] ?? 0) ?: null,
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'status' => $_POST['status'] ?? 'draft',
        'author_id' => Auth::id(),
        'published_at' => $_POST['status'] === 'published' ? date('Y-m-d H:i:s') : null,
    ];

    if (!Auth::canPublish()) {
        $data['status'] = 'draft';
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id) {
        Article::update($id, $data);
    } else {
        Article::create($data);
    }

    Cache::flush();
    Session::flash('success', 'Статья сохранена');
    Router::redirect('admin/articles');
});

$router->post('/articles/delete/{id}', function (array $p) {
    Auth::requireEditor();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) { Router::redirect('admin/articles'); }
    Article::delete((int) $p['id']);
    Cache::flush();
    Session::flash('success', 'Статья удалена');
    Router::redirect('admin/articles');
});

// Media
$router->get('/media', function () use ($admin) {
    Auth::requireAuth();
    $media = Media::getAll(100);
    $admin->render('media/index', compact('media'));
});

$router->post('/media/upload', function () {
    Auth::requireAuth();
    if (!empty($_FILES['file'])) {
        $result = Media::upload($_FILES['file'], Auth::id());
        if ($result) {
            Session::flash('success', 'Файл загружен');
        } else {
            Session::flash('error', 'Ошибка загрузки');
        }
    }
    Router::redirect('admin/media');
});

$router->post('/media/delete/{id}', function (array $p) {
    Auth::requireEditor();
    Media::delete((int) $p['id']);
    Session::flash('success', 'Файл удалён');
    Router::redirect('admin/media');
});

// Menus
$router->get('/menus', function () use ($admin) {
    Auth::requireEditor();
    $menus = Menu::getAllMenus();
    $items = !empty($menus) ? Menu::getItems((int) $menus[0]['id']) : [];
    $admin->render('menus/index', ['menus' => $menus, 'items' => $items, 'menuId' => $menus[0]['id'] ?? 0]);
});

$router->post('/menus/save', function () {
    Auth::requireEditor();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) { Router::redirect('admin/menus'); }

    $menuId = (int) ($_POST['menu_id'] ?? 0);
    $items = json_decode($_POST['items'] ?? '[]', true);
    if ($menuId && is_array($items)) {
        Menu::saveItems($menuId, $items);
        Cache::flush();
        Session::flash('success', 'Меню сохранено');
    }
    Router::redirect('admin/menus');
});

// Users
$router->get('/users', function () use ($admin) {
    Auth::requireAdmin();
    $users = User::getAll();
    $admin->render('users/index', compact('users'));
});

$router->get('/users/create', function () use ($admin) {
    Auth::requireAdmin();
    $admin->render('users/form', ['editUser' => null]);
});

$router->get('/users/edit/{id}', function (array $p) use ($admin) {
    Auth::requireAdmin();
    $editUser = User::getById((int) $p['id']);
    if (!$editUser) { Router::redirect('admin/users'); }
    $admin->render('users/form', compact('editUser'));
});

$router->post('/users/save', function () {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) { Router::redirect('admin/users'); }

    $id = (int) ($_POST['id'] ?? 0);
    $rawLogin = trim($_POST['login'] ?? '');
    $login = Migrate::normalizeLogin($rawLogin);

    if ($login === '') {
        Session::flash('error', 'Укажите корректный логин (латиница, цифры, . _ -)');
        Router::redirect($id ? 'admin/users/edit/' . $id : 'admin/users/create');
    }

    if (User::loginExists($login, $id ?: null)) {
        Session::flash('error', 'Этот логин уже занят');
        Router::redirect($id ? 'admin/users/edit/' . $id : 'admin/users/create');
    }

    $data = [
        'name' => $login,
        'login' => $login,
        'email' => trim($_POST['email'] ?? ''),
        'role' => $_POST['role'] ?? 'author',
        'status' => $_POST['status'] ?? 'active',
    ];

    $password = $_POST['password'] ?? '';

    if ($id) {
        if ($password) {
            $data['password'] = Auth::hashPassword($password);
        }
        User::update($id, $data);
    } else {
        $data['password'] = Auth::hashPassword($password ?: 'password123');
        User::create($data);
    }

    Session::flash('success', 'Пользователь сохранён');
    Router::redirect('admin/users');
});

$router->post('/users/delete/{id}', function (array $p) {
    Auth::requireAdmin();
    if ((int) $p['id'] === Auth::id()) {
        Session::flash('error', 'Нельзя удалить себя');
    } else {
        User::delete((int) $p['id']);
        Session::flash('success', 'Пользователь удалён');
    }
    Router::redirect('admin/users');
});

// Modules
$router->get('/modules', function () use ($admin) {
    Auth::requireAdmin();
    $db = \RuEdu\Engine\Database::getInstance();
    $modules = $db->fetchAll("SELECT * FROM " . $db->table('modules') . " ORDER BY title");
    $admin->render('modules/index', compact('modules'));
});

$router->post('/modules/toggle/{id}', function (array $p) {
    Auth::requireAdmin();
    $db = \RuEdu\Engine\Database::getInstance();
    $mod = $db->fetch("SELECT * FROM " . $db->table('modules') . " WHERE id = ?", [(int) $p['id']]);
    if ($mod) {
        $db->update('modules', ['enabled' => $mod['enabled'] ? 0 : 1], 'id = ?', [(int) $p['id']]);
        Cache::flush();
    }
    Router::redirect('admin/modules');
});

// Updates
$router->get('/updates', function () use ($admin) {
    Auth::requireAdmin();
    $admin->render('updates/index', [
        'currentVersion' => Version::get(),
        'dbVersion' => Version::getDbVersion(),
        'pendingMigrations' => Version::getPendingMigrations(),
        'remoteUpdate' => Updater::checkForUpdate(),
        'backups' => Updater::listBackups(),
        'hasZip' => class_exists('ZipArchive'),
        'updateSource' => Config::get('update_source'),
    ]);
});

$router->post('/updates/migrate', function () {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/updates');
    }

    Migrate::run();
    Cache::flush();

    if (Version::needsDbUpdate()) {
        Session::flash('error', 'Не все миграции выполнены. Проверьте логи сервера.');
    } else {
        Session::flash('success', 'Миграции базы данных выполнены. Версия БД: ' . Version::getDbVersion());
    }
    Router::redirect('admin/updates');
});

$router->post('/updates/upload', function () {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/updates');
    }

    if (empty($_FILES['package']) || $_FILES['package']['error'] !== UPLOAD_ERR_OK) {
        Session::flash('error', 'Не удалось загрузить файл обновления');
        Router::redirect('admin/updates');
    }

    $tmpPath = STORAGE_PATH . '/upload_' . uniqid() . '.zip';
    if (!move_uploaded_file($_FILES['package']['tmp_name'], $tmpPath)) {
        Session::flash('error', 'Ошибка сохранения архива');
        Router::redirect('admin/updates');
    }

    $result = Updater::applyFromZip($tmpPath);
    if ($result['ok']) {
        $msg = 'Обновление установлено. Версия: ' . ($result['version'] ?? Version::get());
        if (!empty($result['backup'])) {
            $msg .= '. Резервная копия: ' . basename($result['backup']);
        }
        Session::flash('success', $msg);
    } else {
        Session::flash('error', $result['error'] ?? 'Ошибка установки обновления');
    }
    Router::redirect('admin/updates');
});

$router->post('/updates/backup', function () {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/updates');
    }

    $backup = Updater::createBackup();
    if ($backup) {
        Session::flash('success', 'Резервная копия создана: ' . basename($backup));
    } else {
        Session::flash('error', 'Не удалось создать резервную копию');
    }
    Router::redirect('admin/updates');
});

// Themes editor
$router->get('/themes', function () use ($admin) {
    Auth::requireAdmin();
    $themes = \RuEdu\Engine\Template::getThemes();
    $admin->render('themes/index', compact('themes'));
});

$router->get('/themes/edit/{slug}', function (array $p) use ($admin) {
    Auth::requireAdmin();

    $slug = $p['slug'] ?? '';
    if (ThemeEditor::getThemeRoot($slug) === null) {
        Session::flash('error', 'Тема не найдена');
        Router::redirect('admin/themes');
    }

    $themes = \RuEdu\Engine\Template::getThemes();
    $theme = null;
    foreach ($themes as $t) {
        if (($t['slug'] ?? '') === $slug) {
            $theme = $t;
            break;
        }
    }
    $theme ??= ['slug' => $slug, 'name' => $slug];

    $files = ThemeEditor::listEditableFiles($slug);
    $requestedFile = isset($_GET['file']) ? (string) $_GET['file'] : '';
    $currentFile = $requestedFile !== '' && in_array($requestedFile, $files, true)
        ? $requestedFile
        : ThemeEditor::defaultFile($slug);

    $fileData = $currentFile ? ThemeEditor::readFile($slug, $currentFile) : null;

    $admin->render('themes/edit', compact('slug', 'theme', 'files', 'currentFile', 'fileData'));
});

$router->post('/themes/save', function () {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/themes');
    }

    $slug = trim($_POST['slug'] ?? '');
    $file = trim($_POST['file'] ?? '');
    $content = $_POST['content'] ?? '';

    if (ThemeEditor::getThemeRoot($slug) === null) {
        Session::flash('error', 'Тема не найдена');
        Router::redirect('admin/themes');
    }

    $result = ThemeEditor::writeFile($slug, $file, $content);
    if ($result !== true) {
        Session::flash('error', $result);
        Router::redirect('admin/themes/edit/' . rawurlencode($slug) . '?file=' . rawurlencode($file));
    }

    Cache::flush();
    Session::flash('success', 'Файл сохранён: ' . $file);
    Router::redirect('admin/themes/edit/' . rawurlencode($slug) . '?file=' . rawurlencode($file));
});

// Settings
$router->get('/settings', function () use ($admin) {
    Auth::requireAdmin();
    $settings = Setting::getAll();
    $themes = \RuEdu\Engine\Template::getThemes();
    $admin->render('settings/index', compact('settings', 'themes'));
});

$router->post('/settings/save', function () {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) { Router::redirect('admin/settings'); }

    $keys = ['site_name', 'site_description', 'site_url', 'admin_email', 'contact_phone',
             'contact_address', 'theme', 'cache_enabled', 'fz152_text', 'cookie_text', 'yandex_map'];

    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            Setting::set($key, trim($_POST[$key]));
        }
    }

    // Update config file for critical settings
    $config = Config::load();
    $config['site_name'] = trim($_POST['site_name'] ?? $config['site_name']);
    $config['site_url'] = rtrim(trim($_POST['site_url'] ?? $config['site_url']), '/');
    $config['base_path'] = Router::basePath();
    $config['theme'] = trim($_POST['theme'] ?? $config['theme']);
    $config['admin_email'] = trim($_POST['admin_email'] ?? $config['admin_email']);
    Config::save($config);

    Cache::flush();
    Session::flash('success', 'Настройки сохранены');
    Router::redirect('admin/settings');
});

// Register module admin routes
Hook::fire('register_admin_routes', $router);

$router->dispatch(Router::getMethod(), Router::getUri());
