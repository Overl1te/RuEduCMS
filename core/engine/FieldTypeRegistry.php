<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class FieldTypeRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function getAll(): array
    {
        $types = [
            'text' => ['label' => 'Текст', 'icon' => 'bi-input-cursor-text', 'has_subfields' => false],
            'textarea' => ['label' => 'Текстовая область', 'icon' => 'bi-textarea-t', 'has_subfields' => false],
            'wysiwyg' => ['label' => 'Редактор', 'icon' => 'bi-file-richtext', 'has_subfields' => false],
            'number' => ['label' => 'Число', 'icon' => 'bi-123', 'has_subfields' => false],
            'color' => ['label' => 'Цвет', 'icon' => 'bi-palette', 'has_subfields' => false],
            'select' => ['label' => 'Выбор', 'icon' => 'bi-ui-radios', 'has_subfields' => false],
            'checkbox' => ['label' => 'Флажок', 'icon' => 'bi-check-square', 'has_subfields' => false],
            'image' => ['label' => 'Изображение', 'icon' => 'bi-image', 'has_subfields' => false],
            'link' => ['label' => 'Ссылка', 'icon' => 'bi-link-45deg', 'has_subfields' => false],
            'repeater' => ['label' => 'Повторитель', 'icon' => 'bi-list-nested', 'has_subfields' => true],
            'group' => ['label' => 'Группа', 'icon' => 'bi-folder', 'has_subfields' => true],
            'flexible' => ['label' => 'Гибкий контент', 'icon' => 'bi-layout-wtf', 'has_subfields' => true],
        ];

        return (array) Hook::fire('register_field_types', $types);
    }

    public static function get(string $type): ?array
    {
        return self::getAll()[$type] ?? null;
    }

    public static function hasSubfields(string $type): bool
    {
        return (bool) (self::get($type)['has_subfields'] ?? false);
    }
}
