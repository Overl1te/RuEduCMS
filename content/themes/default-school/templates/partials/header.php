<header class="site-header">
    <div class="header-top">
        <div class="container">
            <div class="header-top-inner">
                <a href="#" class="a11y-toggle" id="a11yToggle" title="Версия для слабовидящих">
                    <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Версия для слабовидящих
                </a>
                <div class="header-contacts">
                    <?php if ($phone = \RuEdu\Engine\Config::get('contact_phone', '')): ?>
                        <a href="tel:<?= preg_replace('/[^\d+]/', '', $phone) ?>" class="header-phone">
                            <svg class="icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <?= htmlspecialchars($phone) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="header-main">
        <div class="container">
            <div class="header-main-inner">
                <a href="<?= route('') ?>" class="logo">
                    <span class="logo-mark" aria-hidden="true">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none"><rect width="36" height="36" rx="10" fill="currentColor"/><path d="M18 8l8 5v10l-8 5-8-5V13l8-5z" fill="#fff" opacity=".9"/><path d="M18 14l4 2.5v5L18 24l-4-2.5v-5L18 14z" fill="currentColor"/></svg>
                    </span>
                    <span class="logo-text"><?= htmlspecialchars($site_name ?? \RuEdu\Engine\Config::get('site_name', \RuEdu\Engine\Lang::APP_NAME)) ?></span>
                </a>
                <button class="menu-toggle" id="menuToggle" aria-label="Меню" aria-expanded="false">
                    <span class="menu-toggle-bar"></span>
                    <span class="menu-toggle-bar"></span>
                    <span class="menu-toggle-bar"></span>
                </button>
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
