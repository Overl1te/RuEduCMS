<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class BlockRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function getAll(): array
    {
        $blocks = [
            'hero' => [
                'label' => 'Герой',
                'icon' => 'bi-image',
                'context' => 'home',
                'defaults' => [
                    'badge' => 'Официальный сайт',
                    'title' => '',
                    'subtitle' => 'Добро пожаловать на официальный сайт образовательного учреждения — знания, традиции и будущее в одном месте',
                    'buttons' => [
                        ['label' => 'Сведения об ОО', 'url' => '/sveden', 'style' => 'primary'],
                        ['label' => 'Новости', 'url' => '/news', 'style' => 'outline'],
                        ['label' => 'Контакты', 'url' => '/contacts', 'style' => 'outline'],
                    ],
                ],
                'fields' => [
                    ['key' => 'badge', 'label' => 'Бейдж', 'type' => 'text'],
                    ['key' => 'title', 'label' => 'Заголовок (пусто = название сайта)', 'type' => 'text'],
                    ['key' => 'subtitle', 'label' => 'Подзаголовок', 'type' => 'textarea'],
                    ['key' => 'buttons', 'label' => 'Кнопки (JSON)', 'type' => 'json'],
                ],
            ],
            'stats' => [
                'label' => 'Статистика',
                'icon' => 'bi-bar-chart',
                'context' => 'home',
                'defaults' => [
                    'items' => [
                        ['value' => '25+', 'label' => 'Лет опыта', 'count' => '25'],
                        ['value' => '50+', 'label' => 'Педагогов', 'count' => '50'],
                        ['value' => '500+', 'label' => 'Учеников', 'count' => '500'],
                        ['value' => '∞', 'label' => 'Возможностей', 'count' => ''],
                    ],
                ],
                'fields' => [
                    ['key' => 'items', 'label' => 'Элементы (JSON)', 'type' => 'json'],
                ],
            ],
            'quick_links' => [
                'label' => 'Быстрые ссылки',
                'icon' => 'bi-grid',
                'context' => 'home',
                'defaults' => [
                    'eyebrow' => 'Навигация',
                    'title' => 'Быстрые ссылки',
                    'subtitle' => 'Всё важное — в один клик',
                    'links' => [
                        ['label' => 'Информация', 'url' => '/page/informaciya', 'icon' => 'docs'],
                        ['label' => 'Расписание', 'url' => '/schedule', 'icon' => 'schedule'],
                        ['label' => 'Приём в школу', 'url' => '/page/priem-v-shkolu', 'icon' => 'staff'],
                        ['label' => 'Фотоальбомы', 'url' => '/gallery', 'icon' => 'gallery'],
                        ['label' => 'Сведения об ОО', 'url' => '/sveden', 'icon' => 'sveden'],
                        ['label' => 'Контакты', 'url' => '/contacts', 'icon' => 'contacts'],
                    ],
                ],
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Надзаголовок', 'type' => 'text'],
                    ['key' => 'title', 'label' => 'Заголовок', 'type' => 'text'],
                    ['key' => 'subtitle', 'label' => 'Подзаголовок', 'type' => 'text'],
                    ['key' => 'links', 'label' => 'Ссылки (JSON)', 'type' => 'json'],
                ],
            ],
            'latest_news' => [
                'label' => 'Последние новости',
                'icon' => 'bi-newspaper',
                'context' => 'home',
                'defaults' => [
                    'eyebrow' => 'Актуально',
                    'title' => 'Последние новости',
                    'subtitle' => 'События, объявления и достижения',
                    'limit' => 3,
                    'show_all_button' => true,
                ],
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Надзаголовок', 'type' => 'text'],
                    ['key' => 'title', 'label' => 'Заголовок', 'type' => 'text'],
                    ['key' => 'subtitle', 'label' => 'Подзаголовок', 'type' => 'text'],
                    ['key' => 'limit', 'label' => 'Количество', 'type' => 'number'],
                    ['key' => 'show_all_button', 'label' => 'Кнопка «Все новости»', 'type' => 'checkbox'],
                ],
            ],
            'cta' => [
                'label' => 'Призыв к действию',
                'icon' => 'bi-megaphone',
                'context' => 'all',
                'defaults' => [
                    'title' => 'Остались вопросы?',
                    'text' => 'Свяжитесь с нами — мы всегда рады помочь родителям и ученикам',
                    'buttons' => [
                        ['label' => 'Написать нам', 'url' => '/contacts', 'style' => 'primary'],
                        ['label' => 'Сведения об ОО', 'url' => '/sveden', 'style' => 'outline'],
                    ],
                ],
                'fields' => [
                    ['key' => 'title', 'label' => 'Заголовок', 'type' => 'text'],
                    ['key' => 'text', 'label' => 'Текст', 'type' => 'textarea'],
                    ['key' => 'buttons', 'label' => 'Кнопки (JSON)', 'type' => 'json'],
                ],
            ],
            'heading' => [
                'label' => 'Заголовок секции',
                'icon' => 'bi-type-h1',
                'context' => 'page',
                'defaults' => [
                    'eyebrow' => '',
                    'title' => 'Заголовок',
                    'subtitle' => '',
                    'align' => 'left',
                ],
                'fields' => [
                    ['key' => 'eyebrow', 'label' => 'Надзаголовок', 'type' => 'text'],
                    ['key' => 'title', 'label' => 'Заголовок', 'type' => 'text'],
                    ['key' => 'subtitle', 'label' => 'Подзаголовок', 'type' => 'text'],
                    ['key' => 'align', 'label' => 'Выравнивание', 'type' => 'select', 'options' => ['left', 'center']],
                ],
            ],
            'text' => [
                'label' => 'Текст',
                'icon' => 'bi-text-paragraph',
                'context' => 'page',
                'defaults' => [
                    'content' => '<p>Текст страницы</p>',
                ],
                'fields' => [
                    ['key' => 'content', 'label' => 'Содержимое (HTML)', 'type' => 'textarea'],
                ],
            ],
            'columns' => [
                'label' => 'Колонки',
                'icon' => 'bi-columns',
                'context' => 'page',
                'defaults' => [
                    'columns' => [
                        ['title' => 'Колонка 1', 'content' => '<p>Текст первой колонки</p>'],
                        ['title' => 'Колонка 2', 'content' => '<p>Текст второй колонки</p>'],
                    ],
                ],
                'fields' => [
                    ['key' => 'columns', 'label' => 'Колонки (JSON)', 'type' => 'json'],
                ],
            ],
            'spacer' => [
                'label' => 'Отступ',
                'icon' => 'bi-distribute-vertical',
                'context' => 'all',
                'defaults' => [
                    'size' => 'medium',
                ],
                'fields' => [
                    ['key' => 'size', 'label' => 'Размер', 'type' => 'select', 'options' => ['small', 'medium', 'large']],
                ],
            ],
        ];

        return (array) Hook::fire('register_blocks', $blocks);
    }

    public static function get(string $type): ?array
    {
        return self::getAll()[$type] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function forContext(string $context): array
    {
        $all = self::getAll();
        $result = [];

        foreach ($all as $type => $meta) {
            $ctx = (string) ($meta['context'] ?? 'all');
            if ($ctx === 'all' || $ctx === $context) {
                $result[$type] = $meta;
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $props
     * @return array<string, mixed>
     */
    public static function mergeProps(string $type, array $props): array
    {
        $block = self::get($type);
        if ($block === null) {
            return $props;
        }

        return array_merge($block['defaults'] ?? [], $props);
    }
}
