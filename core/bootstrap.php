<?php

declare(strict_types=1);

require_once __DIR__ . '/WebGuard.php';

define('CORE_PATH', __DIR__);
define('CORE_ASSETS_PATH', CORE_PATH . '/assets');
define('ROOT_PATH', dirname(__DIR__));
define('CONTENT_PATH', ROOT_PATH . '/content');
define('THEMES_PATH', CONTENT_PATH . '/themes');
define('MODULES_PATH', CONTENT_PATH . '/modules');
define('UPLOADS_PATH', CONTENT_PATH . '/uploads');
define('LANGUAGES_PATH', CONTENT_PATH . '/languages');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('CONFIG_FILE', ROOT_PATH . '/config.php');

$versionFile = ROOT_PATH . '/VERSION';
define('RUEDU_VERSION', is_file($versionFile) ? (trim((string) file_get_contents($versionFile)) ?: '0.0.1') : '0.0.1');

spl_autoload_register(function (string $class): void {
    $prefix = 'RuEdu\\';
    $baseDir = CORE_PATH . '/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

date_default_timezone_set('Europe/Moscow');

// Миграция: старый config/config.php → config.php в корне
$legacyConfig = ROOT_PATH . '/config/config.php';
if (!file_exists(CONFIG_FILE) && file_exists($legacyConfig)) {
    rename($legacyConfig, CONFIG_FILE);
    @rmdir(ROOT_PATH . '/config');
}

if (file_exists(CONFIG_FILE)) {
    \RuEdu\Engine\Config::load();
}

$secureCookies = str_starts_with((string) \RuEdu\Engine\Config::get('site_url', ''), 'https://');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secureCookies,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (file_exists(CONFIG_FILE) && \RuEdu\Engine\Config::isInstalled()) {
    \RuEdu\Engine\Request::enforcePostSizeLimit();
    \RuEdu\Engine\SecurityHeaders::apply();
}

\RuEdu\Engine\ErrorHandler::register();

if (is_dir(STORAGE_PATH)) {
    $pendingUpdate = \RuEdu\Engine\Updater::applyPending();
    if ($pendingUpdate !== null) {
        if ($pendingUpdate['ok']) {
            \RuEdu\Engine\Session::flash(
                'success',
                'Обновление установлено. Версия: ' . ($pendingUpdate['version'] ?? \RuEdu\Engine\Version::get())
            );
        } elseif (!empty($pendingUpdate['error'])) {
            \RuEdu\Engine\Session::flash('error', 'Ошибка обновления: ' . $pendingUpdate['error']);
        }
    }
}

/**
 * Подпись поля формы на русском языке.
 */
function field_label(string $key): string
{
    return \RuEdu\Engine\Lang::fieldLabel($key);
}

/**
 * Публичный маршрут сайта (/sveden, /news) — для тем и меню.
 */
function route(string $path = ''): string
{
    return \RuEdu\Engine\Router::route($path);
}

/**
 * Статический ресурс (uploads, themes, core/assets, admin/assets).
 */
function asset(string $path = ''): string
{
    return \RuEdu\Engine\Router::asset($path);
}

/**
 * @deprecated Используйте route() для страниц и asset() для файлов
 */
function url(string $path = ''): string
{
    return \RuEdu\Engine\Router::asset($path);
}

/**
 * Рекурсивное удаление директории.
 */
function ruedu_delete_directory(string $dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }

    $items = scandir($dir);
    if ($items === false) {
        return false;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (!ruedu_delete_directory($path)) {
                return false;
            }
            continue;
        }

        if (!is_writable($path)) {
            @chmod($path, 0666);
        }
        if (!@unlink($path) && is_file($path)) {
            return false;
        }
    }

    return @rmdir($dir) || !is_dir($dir);
}
