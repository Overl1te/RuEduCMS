<?php $siteName = \RuEdu\Engine\Config::get('site_name', ''); ?>
<footer class="site-footer">
    <div class="footer-noise" aria-hidden="true"></div>
    <div class="footer-glow" aria-hidden="true"></div>
    <div class="container site-footer__inner">
        <div class="footer-grid">
            <div class="footer-col footer-col--brand">
                <a href="<?= route('') ?>" class="footer-logo" aria-label="На главную">
                    <img src="<?= htmlspecialchars(\RuEdu\Engine\SiteBranding::logoUrl()) ?>" width="48" height="48" alt="">
                </a>
                <p class="footer-brand"><?= htmlspecialchars($siteName) ?></p>
                <p class="footer-tagline"><?= htmlspecialchars(\RuEdu\Engine\Config::get('site_description', '')) ?></p>
            </div>
            <div class="footer-col">
                <h4>Контакты</h4>
                <?php if ($phone = \RuEdu\Engine\Config::get('contact_phone', '')): ?>
                    <p><a href="tel:<?= preg_replace('/[^\d+]/', '', $phone) ?>"><?= htmlspecialchars($phone) ?></a></p>
                <?php endif; ?>
                <?php if ($addr = \RuEdu\Engine\Config::get('contact_address', '')): ?>
                    <p><?= htmlspecialchars($addr) ?></p>
                <?php endif; ?>
                <?php if ($email = \RuEdu\Engine\Config::get('contact_email', '')): ?>
                    <p><a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. Все права защищены.</p>
            <p class="footer-powered">Работает на <a href="https://github.com/RuEduCMS" target="_blank" rel="noopener">RuEduCMS</a></p>
        </div>
    </div>
    <?php if ($siteName !== ''): ?>
    <p class="footer-watermark" aria-hidden="true"><?= htmlspecialchars($siteName) ?></p>
    <?php endif; ?>
</footer>
