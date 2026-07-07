<?php ob_start();
$site_name = $site_name ?? \RuEdu\Engine\Config::get('site_name', '');
$articles = $articles ?? [];

$props = [
    'badge' => 'Официальный сайт',
    'title' => '',
    'subtitle' => 'Добро пожаловать на официальный сайт образовательного учреждения — знания, традиции и будущее в одном месте',
    'buttons' => [
        ['label' => 'Сведения об ОО', 'url' => '/sveden', 'style' => 'primary'],
        ['label' => 'Новости', 'url' => '/news', 'style' => 'outline'],
        ['label' => 'Контакты', 'url' => '/contacts', 'style' => 'outline'],
    ],
];
include __DIR__ . '/blocks/hero.php';

$props = [
    'items' => [
        ['value' => '25+', 'label' => 'Лет опыта', 'count' => '25'],
        ['value' => '50+', 'label' => 'Педагогов', 'count' => '50'],
        ['value' => '500+', 'label' => 'Учеников', 'count' => '500'],
        ['value' => '∞', 'label' => 'Возможностей', 'count' => ''],
    ],
];
include __DIR__ . '/blocks/stats.php';

$props = [
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
];
include __DIR__ . '/blocks/quick_links.php';

$props = [
    'eyebrow' => 'Актуально',
    'title' => 'Последние новости',
    'subtitle' => 'События, объявления и достижения',
    'limit' => 3,
    'show_all_button' => true,
];
include __DIR__ . '/blocks/latest_news.php';

$props = [
    'title' => 'Остались вопросы?',
    'text' => 'Свяжитесь с нами — мы всегда рады помочь родителям и ученикам',
    'buttons' => [
        ['label' => 'Написать нам', 'url' => '/contacts', 'style' => 'primary'],
        ['label' => 'Сведения об ОО', 'url' => '/sveden', 'style' => 'outline'],
    ],
];
include __DIR__ . '/blocks/cta.php';

$content = ob_get_clean();
include __DIR__ . '/layout.php';
