<?php

declare(strict_types=1);

namespace RuEdu\Controller;

class AdminBase
{
    protected function view(string $template, array $data = []): void
    {
        $data['csrf_token'] = \RuEdu\Engine\Session::csrfToken();
        $data['user'] = \RuEdu\Engine\Auth::user();
        $data['flash_success'] = \RuEdu\Engine\Session::flash('success');
        $data['flash_error'] = \RuEdu\Engine\Session::flash('error');
        $data['admin_menu'] = $this->getAdminMenu();

        extract($data);
        $viewFile = ADMIN_PATH . '/views/' . $template . '.php';
        $layoutFile = ADMIN_PATH . '/views/layout/main.php';

        ob_start();
        if (file_exists($viewFile)) {
            include $viewFile;
        }
        $content = ob_get_clean();

        include $layoutFile;
    }

    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function getAdminMenu(): array
    {
        return \RuEdu\Engine\AdminMenu::get();
    }
}
