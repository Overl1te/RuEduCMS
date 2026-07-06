<?php

declare(strict_types=1);

/**
 * Защита от прямого вызова служебных PHP-файлов (нужно для nginx без .htaccess).
 * Подключается через .user.ini и bootstrap.php.
 */
(function (): void {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $script = $_SERVER['SCRIPT_FILENAME'] ?? '';
    if ($script === '' || PHP_SAPI === 'cli') {
        return;
    }

    $root = realpath(dirname(__DIR__));
    $scriptReal = realpath($script);
    if ($root === false || $scriptReal === false) {
        return;
    }

    $rootPrefix = rtrim(str_replace('\\', '/', $root), '/') . '/';
    $scriptPath = str_replace('\\', '/', $scriptReal);
    if (!str_starts_with($scriptPath, $rootPrefix)) {
        return;
    }

    $relative = substr($scriptPath, strlen($rootPrefix));
    $allowed = [
        'index.php',
        'error.php',
        'admin/index.php',
        'install/index.php',
    ];

    if (!in_array($relative, $allowed, true)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Forbidden';
        exit;
    }
})();
