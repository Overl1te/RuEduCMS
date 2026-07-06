<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/core/bootstrap.php';

use RuEdu\Engine\Config;
use RuEdu\Engine\ErrorPage;
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
use RuEdu\Engine\ThemeManager;
use RuEdu\Engine\ThemeInstaller;
use RuEdu\Engine\BlockRegistry;
use RuEdu\Engine\BlockRenderer;
use RuEdu\Engine\FieldGroupEngine;
use RuEdu\Engine\FieldRenderer;
use RuEdu\Engine\FieldValueStore;
use RuEdu\Engine\ElementStyles;
use RuEdu\Model\FieldGroup;
use RuEdu\Engine\SetupRecommendations;
use RuEdu\Engine\SiteSetup;
use RuEdu\Engine\HelpDocs;
use RuEdu\Engine\SystemPages;
use RuEdu\Engine\SearchIndexer;
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
    ErrorPage::send(503, 'CMS не установлена.');
}

Config::load();
Migrate::run();
SearchIndexer::ensureSetup();
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
        Router::redirect(SiteSetup::isRequired() ? 'admin/setup' : 'admin/');
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

// Первоначальная настройка сайта
$router->get('/setup', function () use ($admin) {
    Auth::requireAdmin();
    if (!SiteSetup::isRequired()) {
        Router::redirect('admin/');
    }

    if (isset($_GET['back'])) {
        SiteSetup::setCurrentStep(SiteSetup::prevStep(SiteSetup::getCurrentStep()));
        Router::redirect('admin/setup');
    }

    $admin->render('setup/wizard', [
        'currentStep' => SiteSetup::normalizeStep(SiteSetup::getCurrentStep()),
    ]);
});

$router->post('/setup', function () use ($admin) {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Session::flash('error', 'Ошибка безопасности. Обновите страницу и попробуйте снова.');
        Router::redirect('admin/setup');
    }

    if (!SiteSetup::isRequired()) {
        Router::redirect('admin/');
    }

    $step = (int) ($_POST['step'] ?? 0);
    $errors = SiteSetup::processStep($step, $_POST, $_FILES);

    if ($errors !== []) {
        $admin->render('setup/wizard', [
            'currentStep' => SiteSetup::normalizeStep($step),
            'errors' => $errors,
        ]);
        return;
    }

    if ($step === SiteSetup::STEP_FINISH) {
        Session::flash('success', 'Настройка сайта завершена! Добро пожаловать в панель управления.');
        Router::redirect('admin/');
    }

    Router::redirect('admin/setup');
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

// Help
$renderHelp = function (string $slug) use ($admin): void {
    Auth::requireAuth();

    if (!HelpDocs::sectionExists($slug)) {
        Router::redirect('admin/help/' . HelpDocs::DEFAULT_SLUG);
    }

    $section = HelpDocs::getSection($slug);
    $groupedSections = HelpDocs::getGroupedSections();
    $groups = HelpDocs::getGroups();
    $content = HelpDocs::renderSection($slug);

    $admin->render('help/index', compact('section', 'slug', 'groupedSections', 'groups', 'content'));
};

$router->get('/help', function () use ($renderHelp): void {
    $renderHelp(HelpDocs::DEFAULT_SLUG);
});

$router->get('/help/{slug}', function (array $p) use ($renderHelp): void {
    $renderHelp($p['slug']);
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

    if (isset($_POST['content_mode']) && in_array($_POST['content_mode'], ['html', 'blocks', 'fields'], true)) {
        $data['content_mode'] = $_POST['content_mode'];
    }

    if (!Auth::canPublish() && $data['status'] === 'published') {
        $data['status'] = 'draft';
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id) {
        Page::update($id, $data);
    } else {
        $id = Page::create($data);
        $data['id'] = $id;
    }

    SearchIndexer::onContentPublished('page', $data);
    Hook::fire('content_published', ['type' => 'page', 'data' => $data]);

    Cache::flush();
    Session::flash('success', 'Страница сохранена');
    Router::redirect('admin/pages');
});

$router->get('/pages/builder/{id}', function (array $p) use ($admin) {
    Auth::requireEditor();
    $page = Page::getById((int) ($p['id'] ?? 0));
    if (!$page) {
        Router::redirect('admin/pages');
    }

    $entity = 'page:' . (int) $page['id'];
    $schema = FieldRenderer::getSchemaForEntity($entity);
    $values = $schema['values'];
    if ($values === []) {
        $values = FieldRenderer::normalizeFlexibleValues([]);
    }

    $builderType = 'page';
    $entityKey = $entity;
    $title = 'Конструктор: ' . ($page['title'] ?? '');
    $saveUrl = url('admin/pages/builder/save');
    $backUrl = url('admin/pages/edit/' . (int) $page['id']);
    $previewUrl = route('page/' . ($page['slug'] ?? ''));
    $layouts = $schema['layouts'];
    $page = $page;
    $systemId = null;

    $admin->render('pages/builder', compact('builderType', 'entityKey', 'title', 'saveUrl', 'backUrl', 'previewUrl', 'values', 'layouts', 'page', 'systemId'));
});

$router->post('/pages/builder/save', function () {
    Auth::requireEditor();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/pages');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $page = Page::getById($id);
    if (!$page) {
        Router::redirect('admin/pages');
    }

    $raw = $_POST['field_data'] ?? '[]';
    $decoded = json_decode(is_string($raw) ? $raw : '[]', true);
    $values = FieldRenderer::normalizeFlexibleValues(is_array($decoded) ? $decoded : []);

    FieldValueStore::save('page:' . $id, $values);

    Session::flash('success', 'Структура страницы сохранена');
    Router::redirect('admin/pages/builder/' . $id);
});

$router->get('/home/builder', function () use ($admin) {
    Auth::requireEditor();

    $entity = 'home';
    $schema = FieldRenderer::getSchemaForEntity($entity);
    $values = $schema['values'];
    if ($values === []) {
        $values = FieldRenderer::defaultHomeFieldData();
    }

    $builderType = 'home';
    $entityKey = $entity;
    $title = 'Конструктор главной страницы';
    $saveUrl = url('admin/home/builder/save');
    $backUrl = url('admin/pages');
    $previewUrl = route('');
    $layouts = $schema['layouts'];
    $page = null;
    $systemId = null;

    $admin->render('pages/builder', compact('builderType', 'entityKey', 'title', 'saveUrl', 'backUrl', 'previewUrl', 'values', 'layouts', 'page', 'systemId'));
});

$router->post('/home/builder/save', function () {
    Auth::requireEditor();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/home/builder');
    }

    $raw = $_POST['field_data'] ?? '[]';
    $decoded = json_decode(is_string($raw) ? $raw : '[]', true);
    $values = FieldRenderer::normalizeFlexibleValues(is_array($decoded) ? $decoded : []);

    FieldValueStore::save('home', $values);

    Session::flash('success', 'Главная страница сохранена');
    Router::redirect('admin/home/builder');
});

$router->get('/pages/structure/{id}', function (array $p) use ($admin) {
    Auth::requireEditor();
    $systemId = $p['id'] ?? '';
    $pages = SystemPages::getAll();
    $systemPage = null;
    foreach ($pages as $sp) {
        if (($sp['id'] ?? '') === $systemId) {
            $systemPage = $sp;
            break;
        }
    }
    if ($systemPage === null || !empty($systemPage['content_url'])) {
        Router::redirect('admin/pages');
    }

    $entity = 'system:' . $systemId;
    $schema = FieldRenderer::getSchemaForEntity($entity);
    if ($schema['layouts'] === []) {
        $schema = FieldRenderer::getSchemaForEntity('home');
    }
    $values = FieldValueStore::get($entity);
    if ($values === []) {
        $values = [];
    }

    $builderType = 'system';
    $entityKey = $entity;
    $title = 'Структура: ' . ($systemPage['title'] ?? '');
    $saveUrl = url('admin/pages/structure/save');
    $backUrl = url('admin/pages');
    $previewUrl = route(ltrim((string) ($systemPage['url'] ?? '/'), '/'));
    $layouts = $schema['layouts'];
    $page = null;

    $admin->render('pages/builder', compact('builderType', 'entityKey', 'title', 'saveUrl', 'backUrl', 'previewUrl', 'values', 'layouts', 'page', 'systemId'));
});

$router->post('/pages/structure/save', function () {
    Auth::requireEditor();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/pages');
    }

    $systemId = trim($_POST['system_id'] ?? '');
    if ($systemId === '') {
        Router::redirect('admin/pages');
    }

    $raw = $_POST['field_data'] ?? '[]';
    $decoded = json_decode(is_string($raw) ? $raw : '[]', true);
    $values = FieldRenderer::normalizeFlexibleValues(is_array($decoded) ? $decoded : []);

    FieldValueStore::save('system:' . $systemId, $values);

    Session::flash('success', 'Структура страницы сохранена');
    Router::redirect('admin/pages/structure/' . rawurlencode($systemId));
});

// Field Groups
$router->get('/field-groups', function () use ($admin) {
    Auth::requireAdmin();
    $groups = FieldGroup::getAll();
    $admin->render('field-groups/index', compact('groups'));
});

$router->get('/field-groups/edit/{id}', function (array $p) use ($admin) {
    Auth::requireAdmin();
    $id = (int) ($p['id'] ?? 0);
    $group = $id > 0 ? FieldGroup::getById($id) : null;
    $fieldsTree = $id > 0 ? FieldGroupEngine::buildTree($id) : [];
    $admin->render('field-groups/edit', compact('group', 'fieldsTree'));
});

$router->post('/field-groups/save', function () {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/field-groups');
    }

    $locations = $_POST['locations_json'] ?? '[]';
    if (is_string($locations)) {
        $locations = json_decode($locations, true) ?: [];
    }

    $fieldsTree = $_POST['fields_json'] ?? '[]';
    if (is_string($fieldsTree)) {
        $fieldsTree = json_decode($fieldsTree, true) ?: [];
    }

    $id = FieldGroupEngine::saveGroup([
        'id' => (int) ($_POST['id'] ?? 0),
        'title' => $_POST['title'] ?? '',
        'slug' => $_POST['slug'] ?? '',
        'locations' => $locations,
        'is_active' => !empty($_POST['is_active']),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
    ], is_array($fieldsTree) ? $fieldsTree : []);

    Session::flash('success', 'Группа полей сохранена');
    Router::redirect('admin/field-groups/edit/' . $id);
});

$router->post('/field-groups/delete/{id}', function (array $p) {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/field-groups');
    }
    FieldGroup::delete((int) ($p['id'] ?? 0));
    Cache::flush();
    Session::flash('success', 'Группа удалена');
    Router::redirect('admin/field-groups');
});

$router->get('/api/field-groups/for-entity', function () {
    Auth::requireEditor();
    header('Content-Type: application/json; charset=utf-8');
    $entity = (string) ($_GET['entity'] ?? '');
    echo json_encode(FieldRenderer::getSchemaForEntity($entity), JSON_UNESCAPED_UNICODE);
    exit;
});

$router->get('/api/media', function () {
    Auth::requireEditor();
    header('Content-Type: application/json; charset=utf-8');
    $items = Media::getAll(100);
    foreach ($items as &$item) {
        $item['url'] = Media::getUrl((string) ($item['path'] ?? ''));
    }
    unset($item);
    echo json_encode($items, JSON_UNESCAPED_UNICODE);
    exit;
});

$router->post('/api/preview/render', function () {
    Auth::requireEditor();
    header('Content-Type: application/json; charset=utf-8');
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw ?: '[]', true);
    $rows = is_array($payload['field_data'] ?? null) ? $payload['field_data'] : [];
    $entity = (string) ($payload['entity'] ?? 'home');
    $context = FieldValueStore::getContext($entity);
    if ($entity === 'home') {
        $context['articles'] = Article::getAll('published', 10);
    }
    $html = FieldRenderer::renderFlexibleRows(FieldRenderer::normalizeFlexibleValues($rows), $context);
    echo json_encode(['html' => $html], JSON_UNESCAPED_UNICODE);
    exit;
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
        $id = Article::create($data);
        $data['id'] = $id;
    }

    SearchIndexer::onContentPublished('article', $data);
    Hook::fire('content_published', ['type' => 'article', 'data' => $data]);

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
    $codeModules = [];
    $sectionModules = [];
    foreach ($modules as $module) {
        if (\RuEdu\Engine\Modules::isCodeModule((string) $module['name'])) {
            $codeModules[] = $module;
        } else {
            $sectionModules[] = $module;
        }
    }
    $admin->render('modules/index', compact('codeModules', 'sectionModules'));
});

$router->post('/modules/toggle/{id}', function (array $p) {
    Auth::requireAdmin();
    $db = \RuEdu\Engine\Database::getInstance();
    $mod = $db->fetch("SELECT * FROM " . $db->table('modules') . " WHERE id = ?", [(int) $p['id']]);
    if ($mod) {
        $db->update('modules', ['enabled' => $mod['enabled'] ? 0 : 1], 'id = ?', [(int) $p['id']]);
        \RuEdu\Engine\Modules::resetCache();
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
        'hasPendingUpdate' => Updater::hasPendingUpdate(),
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
        Session::flash('error', 'Сессия истекла. Обновите страницу и повторите загрузку.');
        Router::redirect('admin/updates');
    }

    if (empty($_FILES['package'])) {
        Session::flash('error', 'Файл обновления не получен. Проверьте лимиты upload_max_filesize и post_max_size в PHP.');
        Router::redirect('admin/updates');
    }

    $uploadError = (int) ($_FILES['package']['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadError !== UPLOAD_ERR_OK) {
        $message = match ($uploadError) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Файл слишком большой. Увеличьте upload_max_filesize и post_max_size в PHP.',
            UPLOAD_ERR_PARTIAL => 'Файл загружен частично. Повторите попытку.',
            UPLOAD_ERR_NO_FILE => 'Файл не выбран.',
            default => 'Не удалось загрузить файл обновления (код ' . $uploadError . ').',
        };
        Session::flash('error', $message);
        Router::redirect('admin/updates');
    }

    $tmpPath = STORAGE_PATH . '/upload_' . uniqid() . '.zip';
    if (!move_uploaded_file($_FILES['package']['tmp_name'], $tmpPath)) {
        Session::flash('error', 'Ошибка сохранения архива. Проверьте права на запись в storage/.');
        Router::redirect('admin/updates');
    }

    $result = Updater::applyFromZip($tmpPath);
    if ($result['ok']) {
        $msg = !empty($result['staged'])
            ? 'Архив принят. Установка завершится при переходе на следующую страницу.'
            : 'Обновление установлено. Версия: ' . ($result['version'] ?? Version::get());
        if (!empty($result['backup'])) {
            $msg .= ' Резервная копия: ' . basename($result['backup']);
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
    $activeSlug = ThemeManager::getActiveSlug();
    $hasZip = class_exists('ZipArchive');
    $admin->render('themes/index', compact('themes', 'activeSlug', 'hasZip'));
});

$router->post('/themes/activate', function () {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/themes');
    }

    $slug = trim($_POST['slug'] ?? '');
    $result = ThemeManager::activate($slug);
    if ($result === true) {
        Session::flash('success', 'Тема активирована');
    } else {
        Session::flash('error', $result);
    }
    Router::redirect('admin/themes');
});

$router->post('/themes/install', function () {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Session::flash('error', 'Сессия истекла. Обновите страницу и повторите загрузку.');
        Router::redirect('admin/themes');
    }

    if (empty($_FILES['theme_zip'])) {
        Session::flash('error', 'Файл темы не получен. Проверьте лимиты upload_max_filesize и post_max_size в PHP.');
        Router::redirect('admin/themes');
    }

    $uploadError = (int) ($_FILES['theme_zip']['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadError !== UPLOAD_ERR_OK) {
        $message = match ($uploadError) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Файл слишком большой. Увеличьте upload_max_filesize и post_max_size в PHP.',
            UPLOAD_ERR_PARTIAL => 'Файл загружен частично. Повторите попытку.',
            UPLOAD_ERR_NO_FILE => 'Файл не выбран.',
            default => 'Не удалось загрузить архив темы (код ' . $uploadError . ').',
        };
        Session::flash('error', $message);
        Router::redirect('admin/themes');
    }

    $tmpPath = STORAGE_PATH . '/theme_upload_' . uniqid() . '.zip';
    if (!move_uploaded_file($_FILES['theme_zip']['tmp_name'], $tmpPath)) {
        Session::flash('error', 'Ошибка сохранения архива. Проверьте права на запись в storage/.');
        Router::redirect('admin/themes');
    }

    $result = ThemeInstaller::install($tmpPath);
    if ($result['ok']) {
        $name = $result['name'] ?? $result['slug'] ?? 'тема';
        Session::flash('success', 'Тема «' . $name . '» установлена');
    } else {
        Session::flash('error', $result['error'] ?? 'Ошибка установки темы');
    }
    Router::redirect('admin/themes');
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
    $message = 'Файл сохранён: ' . $file;
    if (str_ends_with(strtolower($file), '.scss')) {
        $message .= '. CSS скомпилирован';
    }
    Session::flash('success', $message);
    Router::redirect('admin/themes/edit/' . rawurlencode($slug) . '?file=' . rawurlencode($file));
});

$router->get('/themes/customize/{slug}', function (array $p) use ($admin) {
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

    $schema = \RuEdu\Engine\ThemeCustomizer::getSchema($slug);
    if (empty($schema['sections'])) {
        Session::flash('error', 'Тема не поддерживает визуальный редактор оформления');
        Router::redirect('admin/themes');
    }

    $admin->render('themes/customize', compact('slug', 'theme'));
});

$router->post('/themes/customize/save', function () {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/themes');
    }

    $slug = trim($_POST['slug'] ?? '');
    if (ThemeEditor::getThemeRoot($slug) === null) {
        Session::flash('error', 'Тема не найдена');
        Router::redirect('admin/themes');
    }

    unset($_POST['_csrf'], $_POST['slug']);
    $result = \RuEdu\Engine\ThemeCustomizer::save($slug, $_POST);
    if ($result !== true) {
        Session::flash('error', $result);
        Router::redirect('admin/themes/customize/' . rawurlencode($slug));
    }

    Session::flash('success', 'Настройки оформления сохранены');
    Router::redirect('admin/themes/customize/' . rawurlencode($slug));
});

$router->post('/themes/customize/reset', function () {
    Auth::requireAdmin();
    if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
        Router::redirect('admin/themes');
    }

    $slug = trim($_POST['slug'] ?? '');
    if (ThemeEditor::getThemeRoot($slug) === null) {
        Session::flash('error', 'Тема не найдена');
        Router::redirect('admin/themes');
    }

    \RuEdu\Engine\ThemeCustomizer::reset($slug);
    Session::flash('success', 'Настройки оформления сброшены');
    Router::redirect('admin/themes/customize/' . rawurlencode($slug));
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

    if (!empty($_POST['site_logo_reset'])) {
        \RuEdu\Engine\SiteBranding::resetLogo();
    } elseif (!empty($_FILES['site_logo']['name'])) {
        if (!\RuEdu\Engine\SiteBranding::uploadLogo($_FILES['site_logo'])) {
            Session::flash('error', 'Не удалось загрузить логотип. Допустимы PNG, JPG, GIF, WebP и ICO.');
            Router::redirect('admin/settings');
        }
    }

    $keys = ['site_name', 'site_description', 'site_url', 'admin_email', 'contact_phone',
             'contact_address', 'theme', 'cache_enabled', 'fz152_text', 'cookie_text', 'yandex_map'];

    if (isset($_POST['theme'])) {
        $themeSlug = trim((string) $_POST['theme']);
        if (!ThemeManager::themeExists($themeSlug)) {
            Session::flash('error', 'Выбранная тема не найдена');
            Router::redirect('admin/settings');
        }
    }

    foreach ($keys as $key) {
        if (!isset($_POST[$key])) {
            continue;
        }

        Setting::set($key, trim((string) $_POST[$key]));
    }

    // Update config file for critical settings
    $config = Config::load();
    $config['site_name'] = trim($_POST['site_name'] ?? $config['site_name']);
    $config['site_url'] = rtrim(trim($_POST['site_url'] ?? $config['site_url']), '/');
    $config['base_path'] = Router::basePath();
    $config['theme'] = trim($_POST['theme'] ?? $config['theme']);
    $config['admin_email'] = trim($_POST['admin_email'] ?? $config['admin_email']);
    $config['seo_indexing'] = isset($_POST['seo_indexing']);
    Config::save($config);

    Cache::flush();
    Session::flash('success', 'Настройки сохранены');
    Router::redirect('admin/settings');
});

// Register module admin routes
Hook::fire('register_admin_routes', $router);

if (SiteSetup::isRequired() && Auth::check() && !SiteSetup::isExemptPath(Router::getUri())) {
    Router::redirect('admin/setup');
}

$router->dispatch(Router::getMethod(), Router::getUri());
