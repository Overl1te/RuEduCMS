<header class="site-header">
    <div class="header-main">
        <div class="container">
            <div class="header-main-inner">
                <a href="<?= route('') ?>" class="logo">
                    <img class="logo-mark" src="<?= htmlspecialchars(\RuEdu\Engine\SiteBranding::logoUrl()) ?>" width="40" height="40" alt="">
                    <span class="logo-text"><?= htmlspecialchars($site_name ?? \RuEdu\Engine\Config::get('site_name', \RuEdu\Engine\Lang::APP_NAME)) ?></span>
                </a>
                <div class="header-main-actions">
                    <?php if ($phone = \RuEdu\Engine\Config::get('contact_phone', '')): ?>
                        <a href="tel:<?= preg_replace('/[^\d+]/', '', $phone) ?>" class="header-phone header-phone--main">
                            <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <span class="header-phone__text"><?= htmlspecialchars($phone) ?></span>
                        </a>
                    <?php endif; ?>
                    <button class="menu-toggle" id="menuToggle" aria-label="Меню" aria-expanded="false">
                        <span class="menu-toggle-bar"></span>
                        <span class="menu-toggle-bar"></span>
                        <span class="menu-toggle-bar"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="header-toolbar">
        <div class="container">
            <div class="header-toolbar-inner">
                <a href="#" class="toolbar-btn toolbar-btn--a11y" id="a11yToggle" title="Версия для слабовидящих">
                    <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>Версия для слабовидящих</span>
                </a>
                <nav class="header-quick-links" aria-label="Быстрые разделы">
                    <a href="<?= route('sveden') ?>" class="toolbar-btn">Сведения об ОО</a>
                    <a href="<?= route('news') ?>" class="toolbar-btn">Новости</a>
                    <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/schedule')): ?>
                    <a href="<?= route('schedule') ?>" class="toolbar-btn">Расписание</a>
                    <?php endif; ?>
                    <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/gallery')): ?>
                    <a href="<?= route('gallery') ?>" class="toolbar-btn">Галерея</a>
                    <?php endif; ?>
                    <a href="<?= route('contacts') ?>" class="toolbar-btn">Контакты</a>
                    <a href="<?= route('sitemap') ?>" class="toolbar-btn">Карта сайта</a>
                </nav>
            </div>
        </div>
    </div>
    <nav class="main-nav" id="mainNav">
        <div class="container">
            <ul class="nav-list">
                <?php foreach ($menu ?? [] as $item): ?>
                    <li><a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
</header>
