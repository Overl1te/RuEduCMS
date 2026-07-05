<header class="site-header">
    <div class="header-top">
        <div class="container">
            <div class="header-top-inner">
                <a href="#" class="a11y-toggle" id="a11yToggle" title="Версия для слабовидящих">
                    <span class="a11y-icon">👁</span> Версия для слабовидящих
                </a>
                <div class="header-contacts">
                    <?php if ($phone = \RuEdu\Engine\Config::get('contact_phone', '')): ?>
                        <span>📞 <?= htmlspecialchars($phone) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="header-main">
        <div class="container">
            <div class="header-main-inner">
                <a href="<?= route('') ?>" class="logo">
                    <span class="logo-icon">🎓</span>
                    <span class="logo-text"><?= htmlspecialchars($site_name ?? \RuEdu\Engine\Config::get('site_name', \RuEdu\Engine\Lang::APP_NAME)) ?></span>
                </a>
                <button class="menu-toggle" id="menuToggle" aria-label="Меню">☰</button>
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
