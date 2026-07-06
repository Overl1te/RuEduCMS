<?php

use RuEdu\Engine\Hook;
use RuEdu\Engine\Router;
use RuEdu\Engine\Template;
use RuEdu\Engine\SEO;
use RuEdu\Engine\Config;
use RuEdu\Engine\Database;
use RuEdu\Engine\Auth;
use RuEdu\Engine\Session;
use RuEdu\Model\Menu;

// Публичный маршрут
Hook::on('register_routes', function ($router) {
    $router->get('/sveden', function () {
        $db = Database::getInstance();
        $sections = $db->fetchAll("SELECT section, data FROM " . $db->table('sveden_data') . " ORDER BY section");
        $data = [];
        foreach ($sections as $s) {
            $data[$s['section']] = json_decode($s['data'], true);
        }

        $sectionList = SvedenModule::getSectionList();
        $sectionFields = [];
        foreach (array_keys($sectionList) as $key) {
            $sectionFields[$key] = SvedenModule::getSectionFields($key);
        }

        $template = new Template();
        echo $template->setData([
            'sections' => $data,
            'sectionList' => $sectionList,
            'sectionFields' => $sectionFields,
            'menu' => Menu::getByLocation('main'),
            'meta' => SEO::metaTags(['title' => 'Сведения об образовательной организации — ' . Config::get('site_name')]),
        ])->render('sveden');
    });
});

Hook::on('admin_menu', function ($menu) {
    $menu[] = ['title' => 'Сведения об ОО', 'url' => Router::path('admin/sveden'), 'icon' => 'bi-building'];
    return $menu;
});

Hook::on('register_admin_routes', function ($router) {
  $router->get('/sveden', function () {
        Auth::requireEditor();
        $db = Database::getInstance();
        $sections = $db->fetchAll("SELECT section, data, updated_at FROM " . $db->table('sveden_data'));
        $data = [];
        foreach ($sections as $s) {
            $data[$s['section']] = json_decode($s['data'], true);
        }
        renderAdminView('index', [
            'sections' => $data,
            'sectionList' => SvedenModule::getSectionList(),
        ]);
    });

    $router->get('/sveden/edit/{section}', function ($params) {
        Auth::requireEditor();
        $section = $params['section'];
        $db = Database::getInstance();
        $row = $db->fetch("SELECT data FROM " . $db->table('sveden_data') . " WHERE section = ?", [$section]);
        $data = $row ? json_decode($row['data'], true) : [];
        $fields = SvedenModule::getSectionFields($section);
        $sectionList = SvedenModule::getSectionList();
        $sectionTitle = $sectionList[$section] ?? $section;

        renderAdminView('form', compact('section', 'sectionTitle', 'data', 'fields'));
    });

    $router->post('/sveden/save', function () {
        Auth::requireEditor();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Router::redirect('admin/sveden');
        }

        $section = $_POST['section'] ?? '';
        $fields = SvedenModule::getSectionFields($section);
        $data = [];
        foreach ($fields as $key => $label) {
            $data[$key] = $_POST[$key] ?? '';
        }

        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM " . $db->table('sveden_data') . " WHERE section = ?", [$section]);

        if ($existing) {
            $db->update('sveden_data', [
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'section = ?', [$section]);
        } else {
            $db->insert('sveden_data', [
                'section' => $section,
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        Session::flash('success', 'Раздел сохранён');
        Router::redirect('admin/sveden');
    });
});

class SvedenModule
{
    public static function getSectionList(): array
    {
        return [
            'common' => 'Основные сведения',
            'structure' => 'Структура и органы управления',
            'documents' => 'Документы',
            'education' => 'Образование',
            'standards' => 'Образовательные стандарты',
            'teachers' => 'Педагогический состав',
            'material' => 'Материально-техническое обеспечение',
            'paid' => 'Платные образовательные услуги',
            'financial' => 'Финансово-хозяйственная деятельность',
            'vacant' => 'Вакантные места',
            'scholarships' => 'Стипендии и меры поддержки',
            'international' => 'Международное сотрудничество',
            'catering' => 'Организация питания',
        ];
    }

    public static function getSectionFields(string $section): array
    {
        $fields = [
            'common' => [
                'full_name' => 'Полное наименование',
                'short_name' => 'Сокращённое наименование',
                'created_date' => 'Дата создания',
                'address' => 'Адрес',
                'work_schedule' => 'Режим и график работы',
                'phone' => 'Телефон',
                'email' => 'Email',
                'license' => 'Лицензия на образовательную деятельность',
                'accreditation' => 'Свидетельство о государственной аккредитации',
            ],
            'structure' => [
                'management' => 'Органы управления',
                'departments' => 'Структурные подразделения',
            ],
            'documents' => [
                'charter' => 'Устав',
                'local_acts' => 'Локальные нормативные акты',
                'reports' => 'Отчёты о результатах самообследования',
            ],
            'education' => [
                'programs' => 'Реализуемые образовательные программы',
                'languages' => 'Языки обучения',
                'forms' => 'Формы обучения',
                'terms' => 'Нормативные сроки обучения',
            ],
            'standards' => [
                'fgos' => 'Федеральные государственные образовательные стандарты',
            ],
            'teachers' => [
                'info' => 'Информация о педагогическом составе',
            ],
            'material' => [
                'buildings' => 'Здания и сооружения',
                'equipment' => 'Оборудование',
                'library' => 'Библиотека',
                'sport' => 'Спортивные объекты',
                'health' => 'Средства обучения и воспитания',
            ],
            'paid' => [
                'services' => 'Платные образовательные услуги',
                'contract' => 'Образец договора',
                'prices' => 'Стоимость обучения',
            ],
            'financial' => [
                'plan' => 'План финансово-хозяйственной деятельности',
                'report' => 'Отчёт о результатах деятельности',
            ],
            'vacant' => [
                'places' => 'Количество вакантных мест',
            ],
            'scholarships' => [
                'info' => 'Стипендии и иные виды поддержки',
                'dormitory' => 'Общежитие, интернат',
            ],
            'international' => [
                'info' => 'Международное сотрудничество',
            ],
            'catering' => [
                'info' => 'Организация питания в образовательной организации',
            ],
        ];

        return $fields[$section] ?? ['content' => 'Содержание раздела'];
    }
}

function renderAdminView(string $template, array $data = []): void
{
    \RuEdu\Engine\AdminView::renderModule('sveden', $template, $data);
}
