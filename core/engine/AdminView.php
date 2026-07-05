<?php

declare(strict_types=1);

namespace RuEdu\Engine;

/**
 * Вспомогательные функции для админ-панели
 */
class AdminView
{
    public static function render(string $template, array $data = []): void
    {
        self::output(ADMIN_PATH . '/views/' . $template . '.php', $data);
    }

    public static function renderModule(string $module, string $template, array $data = []): void
    {
        self::output(MODULES_PATH . '/' . $module . '/admin/' . $template . '.php', $data);
    }

    private static function output(string $viewFile, array $data): void
    {
        $data['csrf_token'] = Session::csrfToken();
        $data['user'] = Auth::user();
        $data['flash_success'] = Session::flash('success');
        $data['flash_error'] = Session::flash('error');
        $data['admin_menu'] = AdminMenu::get();
        extract($data);

        ob_start();
        if (file_exists($viewFile)) {
            include $viewFile;
        }
        $content = ob_get_clean();
        include ADMIN_PATH . '/views/layout/main.php';
    }
}
