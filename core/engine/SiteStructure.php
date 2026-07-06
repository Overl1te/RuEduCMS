<?php

declare(strict_types=1);

namespace RuEdu\Engine;

/**
 * Типовая структура сайта образовательного учреждения
 * (на основе общих разделов портала сайтыобразованию.рф).
 */
class SiteStructure
{
    public const VERSION = 1;

    /**
     * @return list<array{title: string, slug: string, content: string, sort_order: int}>
     */
    public static function defaultPages(): array
    {
        $placeholder = static fn(string $title): string =>
            '<p>Раздел «' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '». '
            . 'Заполните содержимое в панели администратора: <strong>Страницы</strong>.</p>';

        $pages = [
            ['title' => 'Информация', 'slug' => 'informaciya', 'sort_order' => 10],
            ['title' => 'Проекты', 'slug' => 'proekty', 'sort_order' => 11],
            ['title' => 'Обращения граждан', 'slug' => 'obrashcheniya-grazhdan', 'sort_order' => 20],
            ['title' => 'Противодействие коррупции', 'slug' => 'protivodejstvie-korrupcii', 'sort_order' => 21],
            ['title' => 'Дополнительные сведения', 'slug' => 'dopolnitelnye-svedeniya', 'sort_order' => 22],
            ['title' => 'Питание', 'slug' => 'pitanie', 'sort_order' => 23],
            ['title' => 'История школы', 'slug' => 'istoriya-shkoly', 'sort_order' => 24],
            ['title' => 'Приём в школу', 'slug' => 'priem-v-shkolu', 'sort_order' => 25],
            ['title' => 'Приём в 10 класс', 'slug' => 'priem-v-10-klass', 'sort_order' => 26],
            ['title' => 'Приём иностранных граждан', 'slug' => 'priem-inostrannyh-grazhdan', 'sort_order' => 27],
            ['title' => 'Правила приёма обучающихся', 'slug' => 'pravila-priema', 'sort_order' => 28],
            ['title' => 'Школьные СМИ', 'slug' => 'shkolnye-smi', 'sort_order' => 30],
            ['title' => 'Учащимся', 'slug' => 'uchashchimsya', 'sort_order' => 31],
            ['title' => 'Олимпиады и конкурсы', 'slug' => 'olimpiady-konkursy', 'sort_order' => 32],
            ['title' => 'Помощь в кризисной ситуации', 'slug' => 'pomoshch-krizis', 'sort_order' => 33],
            ['title' => 'Воспитательная работа', 'slug' => 'vospitatelnaya-rabota', 'sort_order' => 40],
            ['title' => 'Учителям', 'slug' => 'uchitelyam', 'sort_order' => 41],
            ['title' => 'Аттестация педагогов', 'slug' => 'attestaciya-pedagogov', 'sort_order' => 42],
            ['title' => 'Нормативно-правовые документы', 'slug' => 'normativnye-dokumenty', 'sort_order' => 43],
            ['title' => 'Дополнительное образование', 'slug' => 'dopolnitelnoe-obrazovanie', 'sort_order' => 50],
            ['title' => 'Организация питания', 'slug' => 'organizaciya-pitaniya', 'sort_order' => 51],
            ['title' => 'Безопасность', 'slug' => 'bezopasnost', 'sort_order' => 60],
            ['title' => 'Безопасность дорожного движения', 'slug' => 'bezopasnost-dd', 'sort_order' => 61],
            ['title' => 'Пожарная безопасность', 'slug' => 'pozharnaya-bezopasnost', 'sort_order' => 62],
            ['title' => 'Кибербезопасность', 'slug' => 'kiberbezopasnost', 'sort_order' => 63],
            ['title' => 'Инновационная деятельность', 'slug' => 'innovacionnaya-deyatelnost', 'sort_order' => 70],
            ['title' => 'ГТО', 'slug' => 'gto', 'sort_order' => 71],
            ['title' => 'Психологическая служба', 'slug' => 'psihologicheskaya-sluzhba', 'sort_order' => 72],
            ['title' => 'Телефоны доверия', 'slug' => 'telefony-doveriya', 'sort_order' => 73],
            ['title' => 'Государственная итоговая аттестация', 'slug' => 'gia', 'sort_order' => 80],
            ['title' => 'ГИА-9', 'slug' => 'gia-9', 'sort_order' => 81],
            ['title' => 'ГИА-11', 'slug' => 'gia-11', 'sort_order' => 82],
            ['title' => 'Школьные спортивные клубы', 'slug' => 'sportivnye-kluby', 'sort_order' => 90],
            ['title' => 'Родителям', 'slug' => 'roditelyam', 'sort_order' => 91],
            ['title' => 'Охрана труда', 'slug' => 'ohrana-truda', 'sort_order' => 92],
            ['title' => 'Навигаторы детства', 'slug' => 'navigatory-detstva', 'sort_order' => 93],
        ];

        return array_map(static function (array $page) use ($placeholder): array {
            $page['content'] = $placeholder($page['title']);

            return $page;
        }, $pages);
    }

    /**
     * Главное меню (шапка сайта).
     *
     * @return list<array{title: string, url: string, target?: string, children?: list<array{title: string, url: string, target?: string}>}>
     */
    public static function mainMenu(): array
    {
        return [
            ['title' => 'Сведения об образовательной организации', 'url' => '/sveden'],
            ['title' => 'Новости', 'url' => '/news'],
            ['title' => 'Информация', 'url' => '/page/informaciya'],
            ['title' => 'Проекты', 'url' => '/page/proekty'],
            ['title' => 'Фотоальбомы', 'url' => '/gallery'],
            ['title' => 'Контакты', 'url' => '/contacts'],
        ];
    }

    /**
     * Боковое меню (типовые разделы школьного сайта).
     *
     * @return list<array{title: string, url: string, target?: string, children?: list<array{title: string, url: string, target?: string}>}>
     */
    public static function sideMenu(): array
    {
        return [
            ['title' => 'Обращения граждан', 'url' => '/page/obrashcheniya-grazhdan'],
            ['title' => 'Противодействие коррупции', 'url' => '/page/protivodejstvie-korrupcii'],
            ['title' => 'Дополнительные сведения', 'url' => '/page/dopolnitelnye-svedeniya'],
            ['title' => 'Питание', 'url' => '/page/pitanie'],
            ['title' => 'История школы', 'url' => '/page/istoriya-shkoly'],
            [
                'title' => 'Приём в школу',
                'url' => '/page/priem-v-shkolu',
                'children' => [
                    ['title' => 'Приём в 10 класс', 'url' => '/page/priem-v-10-klass'],
                    ['title' => 'Приём иностранных граждан', 'url' => '/page/priem-inostrannyh-grazhdan'],
                    ['title' => 'Правила приёма', 'url' => '/page/pravila-priema'],
                ],
            ],
            ['title' => 'Школьные СМИ', 'url' => '/page/shkolnye-smi'],
            [
                'title' => 'Учащимся',
                'url' => '/page/uchashchimsya',
                'children' => [
                    ['title' => 'Олимпиады и конкурсы', 'url' => '/page/olimpiady-konkursy'],
                    ['title' => 'Помощь в кризисной ситуации', 'url' => '/page/pomoshch-krizis'],
                ],
            ],
            ['title' => 'Расписание', 'url' => '/schedule'],
            ['title' => 'Воспитательная работа', 'url' => '/page/vospitatelnaya-rabota'],
            [
                'title' => 'Учителям',
                'url' => '/page/uchitelyam',
                'children' => [
                    ['title' => 'Аттестация', 'url' => '/page/attestaciya-pedagogov'],
                    ['title' => 'Нормативные документы', 'url' => '/page/normativnye-dokumenty'],
                ],
            ],
            ['title' => 'Электронный дневник', 'url' => 'https://edu.gosuslugi.ru/', 'target' => '_blank'],
            ['title' => 'Дополнительное образование', 'url' => '/page/dopolnitelnoe-obrazovanie'],
            ['title' => 'Организация питания', 'url' => '/page/organizaciya-pitaniya'],
            [
                'title' => 'Безопасность',
                'url' => '/page/bezopasnost',
                'children' => [
                    ['title' => 'Безопасность ДД', 'url' => '/page/bezopasnost-dd'],
                    ['title' => 'Пожарная безопасность', 'url' => '/page/pozharnaya-bezopasnost'],
                    ['title' => 'Кибербезопасность', 'url' => '/page/kiberbezopasnost'],
                ],
            ],
            ['title' => 'Инновационная деятельность', 'url' => '/page/innovacionnaya-deyatelnost'],
            ['title' => 'ГТО', 'url' => '/page/gto'],
            [
                'title' => 'Психологическая служба',
                'url' => '/page/psihologicheskaya-sluzhba',
                'children' => [
                    ['title' => 'Телефоны доверия', 'url' => '/page/telefony-doveriya'],
                ],
            ],
            [
                'title' => 'Государственная итоговая аттестация',
                'url' => '/page/gia',
                'children' => [
                    ['title' => 'ГИА-9', 'url' => '/page/gia-9'],
                    ['title' => 'ГИА-11', 'url' => '/page/gia-11'],
                ],
            ],
            ['title' => 'Документы организации', 'url' => '/documents'],
            ['title' => 'Школьные спортивные клубы', 'url' => '/page/sportivnye-kluby'],
            ['title' => 'Родителям', 'url' => '/page/roditelyam'],
            ['title' => 'Охрана труда', 'url' => '/page/ohrana-truda'],
            ['title' => 'Навигаторы детства', 'url' => '/page/navigatory-detstva'],
        ];
    }

    /**
     * Дерево для HTML-карты сайта.
     *
     * @return list<array{title: string, url: string, children: list<mixed>}>
     */
    public static function sitemapTree(): array
    {
        $tree = [
            ['title' => 'Главная', 'url' => '/', 'children' => []],
        ];

        foreach (self::mainMenu() as $item) {
            $tree[] = self::normalizeTreeItem($item);
        }

        $sideRoot = [
            'title' => 'Разделы сайта',
            'url' => '#',
            'children' => [],
        ];
        foreach (self::sideMenu() as $item) {
            $sideRoot['children'][] = self::normalizeTreeItem($item);
        }
        $tree[] = $sideRoot;

        $tree[] = [
            'title' => 'Служебные страницы',
            'url' => '#',
            'children' => [
                ['title' => 'Карта сайта', 'url' => '/sitemap', 'children' => []],
                ['title' => 'Педагогический состав', 'url' => '/staff', 'children' => []],
            ],
        ];

        return $tree;
    }

    public static function seed(\PDO $pdo, string $prefix, bool $replaceMenus = false): void
    {
        if (!self::isSeeded($pdo, $prefix) || $replaceMenus) {
            self::seedPages($pdo, $prefix);
            self::seedMenus($pdo, $prefix, $replaceMenus);
            self::markSeeded($pdo, $prefix);
        }
    }

    public static function seedForMigration(\PDO $pdo, string $prefix): void
    {
        if (self::isSeeded($pdo, $prefix)) {
            return;
        }

        $shouldReplaceMenus = self::hasLegacyMainMenu($pdo, $prefix);
        self::seedPages($pdo, $prefix);
        self::seedMenus($pdo, $prefix, $shouldReplaceMenus);
        self::markSeeded($pdo, $prefix);
    }

    private static function seedPages(\PDO $pdo, string $prefix): void
    {
        $table = $prefix . 'pages';
        if (!Migrate::tableExists($pdo, $table)) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $check = $pdo->prepare("SELECT id FROM `{$table}` WHERE slug = ? LIMIT 1");
        $insert = $pdo->prepare(
            "INSERT INTO `{$table}` (title, slug, content, status, sort_order, created_at, updated_at)
             VALUES (?, ?, ?, 'published', ?, ?, ?)"
        );

        foreach (self::defaultPages() as $page) {
            $check->execute([$page['slug']]);
            if ($check->fetchColumn()) {
                continue;
            }

            $insert->execute([
                $page['title'],
                $page['slug'],
                $page['content'],
                $page['sort_order'],
                $now,
                $now,
            ]);
        }
    }

    private static function seedMenus(\PDO $pdo, string $prefix, bool $replace): void
    {
        $menusTable = $prefix . 'menus';
        $itemsTable = $prefix . 'menu_items';

        if (!Migrate::tableExists($pdo, $menusTable) || !Migrate::tableExists($pdo, $itemsTable)) {
            return;
        }

        $mainMenuId = self::ensureMenu($pdo, $prefix, 'Главное меню', 'main');
        $sideMenuId = self::ensureMenu($pdo, $prefix, 'Боковое меню', 'side');

        if ($replace || self::countMenuItems($pdo, $prefix, $mainMenuId) === 0) {
            $pdo->exec("DELETE FROM `{$itemsTable}` WHERE menu_id = {$mainMenuId}");
            self::insertMenuItems($pdo, $prefix, $mainMenuId, self::mainMenu());
        }

        if ($replace || self::countMenuItems($pdo, $prefix, $sideMenuId) === 0) {
            $pdo->exec("DELETE FROM `{$itemsTable}` WHERE menu_id = {$sideMenuId}");
            self::insertMenuItems($pdo, $prefix, $sideMenuId, self::sideMenu());
        }
    }

    /**
     * @param list<array{title: string, url: string, target?: string, children?: list<array{title: string, url: string, target?: string}>}> $items
     */
    private static function insertMenuItems(\PDO $pdo, string $prefix, int $menuId, array $items, ?int $parentId = null, int $order = 0): void
    {
        $table = $prefix . 'menu_items';
        $stmt = $pdo->prepare(
            "INSERT INTO `{$table}` (menu_id, parent_id, title, url, target, sort_order)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        foreach ($items as $item) {
            $target = $item['target'] ?? '_self';
            $stmt->execute([$menuId, $parentId, $item['title'], $item['url'], $target, $order]);
            $itemId = (int) $pdo->lastInsertId();

            if (!empty($item['children'])) {
                self::insertMenuItems($pdo, $prefix, $menuId, $item['children'], $itemId, 0);
            }

            $order++;
        }
    }

    private static function ensureMenu(\PDO $pdo, string $prefix, string $name, string $location): int
    {
        $table = $prefix . 'menus';
        $stmt = $pdo->prepare("SELECT id FROM `{$table}` WHERE location = ? LIMIT 1");
        $stmt->execute([$location]);
        $id = $stmt->fetchColumn();

        if ($id) {
            return (int) $id;
        }

        $insert = $pdo->prepare("INSERT INTO `{$table}` (name, location, created_at) VALUES (?, ?, ?)");
        $insert->execute([$name, $location, date('Y-m-d H:i:s')]);

        return (int) $pdo->lastInsertId();
    }

    private static function isSeeded(\PDO $pdo, string $prefix): bool
    {
        $settings = $prefix . 'settings';
        if (!Migrate::tableExists($pdo, $settings)) {
            return false;
        }

        $stmt = $pdo->prepare("SELECT value FROM `{$settings}` WHERE `key` = 'site_structure_version' LIMIT 1");
        $stmt->execute();
        $value = $stmt->fetchColumn();

        return (int) $value >= self::VERSION;
    }

    private static function markSeeded(\PDO $pdo, string $prefix): void
    {
        $settings = $prefix . 'settings';
        if (!Migrate::tableExists($pdo, $settings)) {
            return;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO `{$settings}` (`key`, `value`) VALUES ('site_structure_version', ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
        );
        $stmt->execute([(string) self::VERSION]);
    }

    private static function hasLegacyMainMenu(\PDO $pdo, string $prefix): bool
    {
        $menus = $prefix . 'menus';
        $items = $prefix . 'menu_items';

        $stmt = $pdo->prepare("SELECT id FROM `{$menus}` WHERE location = 'main' LIMIT 1");
        $stmt->execute();
        $menuId = $stmt->fetchColumn();

        if (!$menuId) {
            return true;
        }

        $legacy = [
            ['Главная', '/'],
            ['Сведения об ОО', '/sveden'],
            ['Новости', '/news'],
            ['Педагогический состав', '/staff'],
            ['Расписание', '/schedule'],
            ['Документы', '/documents'],
            ['Галерея', '/gallery'],
            ['Контакты', '/contacts'],
        ];

        $stmt = $pdo->prepare(
            "SELECT title, url FROM `{$items}` WHERE menu_id = ? AND parent_id IS NULL ORDER BY sort_order"
        );
        $stmt->execute([(int) $menuId]);
        $current = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($current) !== count($legacy)) {
            return false;
        }

        foreach ($current as $i => $row) {
            if (($row['title'] ?? '') !== $legacy[$i][0] || ($row['url'] ?? '') !== $legacy[$i][1]) {
                return false;
            }
        }

        return true;
    }

    private static function countMenuItems(\PDO $pdo, string $prefix, int $menuId): int
    {
        $table = $prefix . 'menu_items';
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE menu_id = ?");
        $stmt->execute([$menuId]);

        return (int) $stmt->fetchColumn();
    }

    private static function countSideMenuItems(\PDO $pdo, string $prefix): int
    {
        $menus = $prefix . 'menus';
        $items = $prefix . 'menu_items';

        $stmt = $pdo->prepare("SELECT id FROM `{$menus}` WHERE location = 'side' LIMIT 1");
        $stmt->execute();
        $menuId = $stmt->fetchColumn();

        if (!$menuId) {
            return 0;
        }

        return self::countMenuItems($pdo, $prefix, (int) $menuId);
    }

    /**
     * @param array{title: string, url: string, target?: string, children?: list<array{title: string, url: string, target?: string}>} $item
     * @return array{title: string, url: string, children: list<mixed>}
     */
    private static function normalizeTreeItem(array $item): array
    {
        $node = [
            'title' => $item['title'],
            'url' => $item['url'],
            'children' => [],
        ];

        foreach ($item['children'] ?? [] as $child) {
            $node['children'][] = self::normalizeTreeItem($child);
        }

        return $node;
    }
}
