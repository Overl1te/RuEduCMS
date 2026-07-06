<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use RuEdu\Model\Menu;

class ErrorPage
{
    private const DEFINITIONS = [
        400 => [
            'title' => 'Неверный запрос',
            'message' => 'Сервер не может обработать запрос из-за некорректных данных.',
        ],
        401 => [
            'title' => 'Требуется авторизация',
            'message' => 'Для доступа к этой странице необходимо войти в систему.',
        ],
        403 => [
            'title' => 'Доступ запрещён',
            'message' => 'У вас нет прав для просмотра этой страницы, либо запрошенный ресурс недоступен.',
        ],
        404 => [
            'title' => 'Страница не найдена',
            'message' => 'Запрашиваемая страница не существует, была удалена или адрес введён с ошибкой.',
        ],
        500 => [
            'title' => 'Ошибка сервера',
            'message' => 'На сервере произошла ошибка. Пожалуйста, повторите попытку позже.',
        ],
        503 => [
            'title' => 'Сервис недоступен',
            'message' => 'Сайт временно недоступен. Пожалуйста, повторите попытку позже.',
        ],
    ];

    public static function send(int $code, ?string $message = null): void
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: text/html; charset=UTF-8');
        }

        echo self::render($code, $message);
        exit;
    }

    public static function render(int $code, ?string $message = null): string
    {
        $definition = self::definition($code);
        $title = $definition['title'];
        $message ??= $definition['message'];

        if (Config::isInstalled()) {
            try {
                Config::load();

                $template = new Template();
                $themePath = THEMES_PATH . '/' . Config::get('theme', 'default-school');
                $specificTemplate = $themePath . '/templates/' . $code . '.php';
                $templateName = is_file($specificTemplate) ? (string) $code : 'error';

                return $template->setData([
                    'menu' => Menu::getByLocation('main'),
                    'site_name' => Config::get('site_name'),
                    'error_code' => $code,
                    'error_title' => $title,
                    'error_message' => $message,
                    'meta' => SEO::metaTags([
                        'title' => $title . ' — ' . Config::get('site_name'),
                    ]),
                ])->render($templateName);
            } catch (\Throwable) {
            }
        }

        return self::renderFallback($code, $title, $message);
    }

    public static function definition(int $code): array
    {
        return self::DEFINITIONS[$code] ?? self::DEFINITIONS[500];
    }

    public static function detectCode(): int
    {
        if (isset($_GET['code']) && is_numeric($_GET['code'])) {
            return (int) $_GET['code'];
        }

        foreach (['REDIRECT_STATUS', 'REDIRECT_REDIRECT_STATUS'] as $key) {
            if (!empty($_SERVER[$key]) && is_numeric($_SERVER[$key])) {
                return (int) $_SERVER[$key];
            }
        }

        return 404;
    }

    private static function renderFallback(int $code, string $title, string $message): string
    {
        $siteName = Lang::APP_NAME;

        ob_start();
        include CORE_PATH . '/views/error.php';

        return (string) ob_get_clean();
    }
}
