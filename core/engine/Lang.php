<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Lang
{
    public const APP_NAME = 'RuEduCMS';

    private static array $fieldLabels = [
        'name' => 'Имя',
        'email' => 'Email',
        'message' => 'Сообщение',
        'phone' => 'Телефон',
        'consent' => 'Согласие',
    ];

    public static function appName(): string
    {
        try {
            if (class_exists(Config::class) && Config::isInstalled()) {
                $name = (string) Config::get('site_name', '');
                if ($name !== '') {
                    return $name;
                }
            }
        } catch (\Throwable) {
        }

        return self::APP_NAME;
    }

    public static function fieldLabel(string $key): string
    {
        return self::$fieldLabels[$key] ?? $key;
    }

    public static function role(string $role): string
    {
        return match ($role) {
            'admin' => 'Администратор',
            'editor' => 'Редактор',
            'author' => 'Автор',
            default => $role,
        };
    }

    public static function userStatus(string $status): string
    {
        return match ($status) {
            'active' => 'Активен',
            'inactive' => 'Неактивен',
            default => $status,
        };
    }

    public static function publishStatus(string $status): string
    {
        return match ($status) {
            'published' => 'Опубликована',
            'draft' => 'Черновик',
            default => $status,
        };
    }

    public static function formStatus(string $status): string
    {
        return match ($status) {
            'active' => 'Активна',
            'inactive' => 'Неактивна',
            default => $status,
        };
    }
}
