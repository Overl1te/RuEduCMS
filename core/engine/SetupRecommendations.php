<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use RuEdu\Model\Setting;

class SetupRecommendations
{
    private const SVEDEN_SECTIONS = [
        'common',
        'structure',
        'documents',
        'education',
        'standards',
        'teachers',
        'material',
        'paid',
        'financial',
        'vacant',
        'scholarships',
        'international',
        'catering',
    ];

    private const COMMON_FIELDS = ['full_name', 'address', 'phone', 'email'];

    /**
     * @return list<array{title: string, description: string, url: string, icon: string, priority: int, progress?: int}>
     */
    public static function getAll(): array
    {
        $items = [];

        if (self::isModuleEnabled('sveden')) {
            $items = array_merge($items, self::svedenRecommendations());
        }

        if (self::isModuleEnabled('documents') && self::count('documents') === 0) {
            $items[] = [
                'title' => 'Загрузите документы',
                'description' => 'Добавьте устав, локальные акты и другие документы учреждения.',
                'url' => Router::path('admin/documents'),
                'icon' => 'bi-file-earmark-pdf',
                'priority' => 15,
            ];
        }

        if (self::isModuleEnabled('staff') && self::count('staff') === 0) {
            $items[] = [
                'title' => 'Добавьте педагогический состав',
                'description' => 'Разместите информацию о сотрудниках и руководстве школы.',
                'url' => Router::path('admin/staff'),
                'icon' => 'bi-person-badge',
                'priority' => 20,
            ];
        }

        if (self::isModuleEnabled('schedule') && self::count('schedule') === 0) {
            $items[] = [
                'title' => 'Заполните расписание',
                'description' => 'Добавьте расписание занятий для классов.',
                'url' => Router::path('admin/schedule'),
                'icon' => 'bi-calendar3',
                'priority' => 25,
            ];
        }

        if (self::needsContactSettings()) {
            $items[] = [
                'title' => 'Укажите контактные данные',
                'description' => 'Заполните телефон, адрес и карту на странице контактов.',
                'url' => Router::path('admin/settings'),
                'icon' => 'bi-telephone',
                'priority' => 10,
            ];
        }

        if (self::isModuleEnabled('news') && self::count('articles', "status = 'published'") === 0) {
            $items[] = [
                'title' => 'Опубликуйте первую новость',
                'description' => 'Расскажите посетителям о событиях и объявлениях учреждения.',
                'url' => Router::path('admin/articles/create'),
                'icon' => 'bi-newspaper',
                'priority' => 30,
            ];
        }

        if (self::isModuleEnabled('gallery') && self::count('gallery_albums') === 0) {
            $items[] = [
                'title' => 'Создайте фотогалерею',
                'description' => 'Добавьте альбомы с фотографиями жизни учреждения.',
                'url' => Router::path('admin/gallery'),
                'icon' => 'bi-camera',
                'priority' => 35,
            ];
        }

        if (self::hasDefaultDescription()) {
            $items[] = [
                'title' => 'Обновите описание сайта',
                'description' => 'Замените стандартное описание на информацию о вашем учреждении.',
                'url' => Router::path('admin/settings'),
                'icon' => 'bi-pencil-square',
                'priority' => 40,
            ];
        }

        usort($items, static fn(array $a, array $b): int => $a['priority'] <=> $b['priority']);

        return $items;
    }

    /**
     * @return list<array{title: string, description: string, url: string, icon: string, priority: int, progress?: int}>
     */
    private static function svedenRecommendations(): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll('SELECT section, data FROM ' . $db->table('sveden_data'));
        $sections = [];
        foreach ($rows as $row) {
            $sections[$row['section']] = json_decode($row['data'], true) ?: [];
        }

        $items = [];
        $common = $sections['common'] ?? [];

        if (!self::hasRequiredFields($common, self::COMMON_FIELDS)) {
            $items[] = [
                'title' => 'Заполните основные сведения об организации',
                'description' => 'Укажите наименование, адрес, телефон и email — это обязательный раздел для сайта ОО.',
                'url' => Router::path('admin/sveden/edit/common'),
                'icon' => 'bi-building',
                'priority' => 1,
            ];

            return $items;
        }

        $filled = 0;
        foreach (self::SVEDEN_SECTIONS as $section) {
            if (self::sectionHasContent($sections[$section] ?? [])) {
                $filled++;
            }
        }

        $total = count(self::SVEDEN_SECTIONS);
        if ($filled < $total) {
            $items[] = [
                'title' => 'Дополните раздел «Сведения об ОО»',
                'description' => "Заполнено {$filled} из {$total} подразделов. Раздел обязателен для образовательных организаций.",
                'url' => Router::path('admin/sveden'),
                'icon' => 'bi-journal-text',
                'priority' => 5,
                'progress' => (int) round($filled / $total * 100),
            ];
        }

        return $items;
    }

    private static function isModuleEnabled(string $name): bool
    {
        $db = Database::getInstance();
        $row = $db->fetch(
            'SELECT enabled FROM ' . $db->table('modules') . ' WHERE name = ?',
            [$name]
        );

        return $row && (int) $row['enabled'] === 1;
    }

    private static function count(string $table, string $where = '1=1', array $params = []): int
    {
        return Database::getInstance()->count($table, $where, $params);
    }

    private static function needsContactSettings(): bool
    {
        $phone = trim((string) Setting::get('contact_phone', ''));
        $address = trim((string) Setting::get('contact_address', ''));

        return $phone === '' || $address === '';
    }

    private static function hasDefaultDescription(): bool
    {
        $description = trim((string) Config::get('site_description', ''));

        return $description === '' || $description === 'Сайт образовательного учреждения';
    }

    private static function hasRequiredFields(array $data, array $fields): bool
    {
        foreach ($fields as $field) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                return false;
            }
        }

        return true;
    }

    private static function sectionHasContent(array $data): bool
    {
        foreach ($data as $value) {
            if (trim(strip_tags((string) $value)) !== '') {
                return true;
            }
        }

        return false;
    }
}
