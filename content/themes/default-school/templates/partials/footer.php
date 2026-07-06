<?php $siteName = \RuEdu\Engine\Config::get('site_name', ''); ?>
<footer class="site-footer">
    <div class="footer-noise" aria-hidden="true"></div>
    <div class="footer-glow" aria-hidden="true"></div>
    <div class="container site-footer__inner">
        <div class="footer-grid">
            <div class="footer-col footer-col--brand">
                <a href="<?= route('') ?>" class="footer-logo" aria-label="На главную">
                    <img src="<?= htmlspecialchars(\RuEdu\Engine\SiteBranding::logoUrl()) ?>" width="56" height="56" alt="">
                </a>
                <p class="footer-brand"><?= htmlspecialchars($siteName) ?></p>
                <p class="footer-tagline"><?= htmlspecialchars(\RuEdu\Engine\Config::get('site_description', '')) ?></p>
            </div>
            <div class="footer-col">
                <h4>Разделы</h4>
                <ul class="footer-links">
                    <li><a href="<?= route('sveden') ?>">Сведения об ОО</a></li>
                    <li><a href="<?= route('news') ?>">Новости</a></li>
                    <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/schedule')): ?>
                    <li><a href="<?= route('schedule') ?>">Расписание</a></li>
                    <?php endif; ?>
                    <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/gallery')): ?>
                    <li><a href="<?= route('gallery') ?>">Галерея</a></li>
                    <?php endif; ?>
                    <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/staff')): ?>
                    <li><a href="<?= route('staff') ?>">Педагогический состав</a></li>
                    <?php endif; ?>
                    <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/documents')): ?>
                    <li><a href="<?= route('documents') ?>">Документы</a></li>
                    <?php endif; ?>
                    <li><a href="<?= route('contacts') ?>">Контакты</a></li>
                    <li><a href="<?= route('sitemap') ?>">Карта сайта</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Контакты</h4>
                <ul class="footer-contacts">
                    <?php if ($phone = \RuEdu\Engine\Config::get('contact_phone', '')): ?>
                    <li>
                        <span class="footer-contact-label">Телефон</span>
                        <a href="tel:<?= preg_replace('/[^\d+]/', '', $phone) ?>"><?= htmlspecialchars($phone) ?></a>
                    </li>
                    <?php endif; ?>
                    <?php if ($email = \RuEdu\Engine\Config::get('contact_email', '')): ?>
                    <li>
                        <span class="footer-contact-label">Email</span>
                        <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
                    </li>
                    <?php endif; ?>
                    <?php if ($addr = \RuEdu\Engine\Config::get('contact_address', '')): ?>
                    <li>
                        <span class="footer-contact-label">Адрес</span>
                        <span><?= htmlspecialchars($addr) ?></span>
                    </li>
                    <?php endif; ?>
                </ul>
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
