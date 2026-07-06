<header class="ed-header" id="edHeader">
    <div class="ed-container ed-header__inner">
        <a href="<?= route('') ?>" class="ed-logo">
            <img class="ed-logo__mark" src="<?= htmlspecialchars(\RuEdu\Engine\SiteBranding::logoUrl()) ?>" width="32" height="32" alt="">
            <span class="ed-logo__text"><?= htmlspecialchars($site_name ?? \RuEdu\Engine\Config::get('site_name', \RuEdu\Engine\Lang::APP_NAME)) ?></span>
        </a>
        <div class="ed-header__actions">
            <?php if ($phone = \RuEdu\Engine\Config::get('contact_phone', '')): ?>
                <a href="tel:<?= preg_replace('/[^\d+]/', '', $phone) ?>" class="ed-header__phone">
                    <?= htmlspecialchars($phone) ?>
                </a>
            <?php endif; ?>
            <a href="#" class="ed-a11y-toggle" id="a11yToggle" title="Версия для слабовидящих">Aa</a>
            <button class="ed-menu-toggle" id="menuToggle" aria-label="Меню" aria-expanded="false">
                <span></span><span></span>
            </button>
        </div>
    </div>
    <nav class="ed-nav" id="mainNav">
        <div class="ed-container">
            <ul class="ed-nav__list">
                <?php foreach ($menu ?? [] as $item): ?>
                    <li><a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
</header>
