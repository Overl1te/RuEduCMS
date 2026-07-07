<?php

declare(strict_types=1);

/**
 * Пример config.php для RuEduCMS.
 * Скопируйте в config.php и настройте под свой сервер.
 *
 * Параметры безопасности задаются только здесь — в админке их нет.
 */
return [
    'timezone' => 'Europe/Moscow',
    'language' => 'ru',
    'debug' => false,
    'update_source' => null,

    // --- Безопасность (только config.php) ---

    // IP reverse proxy, которым доверяем заголовок X-Forwarded-For
    'trusted_proxies' => [],

    // Капча (GD, без внешних сервисов)
    'captcha_enabled' => true,
    'captcha_on_forms' => true,
    'captcha_on_login' => false,
    'captcha_length' => 5,
    // Капча на логине после N неудачных попыток (если captcha_on_login = false)
    'captcha_login_after_failures' => 2,

    // Лимит размера POST для публичных форм (байты). Админка не ограничивается.
    'post_max_bytes' => 10485760,

    // Очистка записей rate_limits старше N часов
    'rate_limit_cleanup_hours' => 24,
];
