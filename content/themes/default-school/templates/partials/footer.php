<footer class="site-footer">
    <svg class="footer-wave" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0,40 C360,80 720,0 1080,40 C1260,60 1380,50 1440,40 L1440,0 L0,0 Z"/>
    </svg>
    <div class="footer-glow" aria-hidden="true"></div>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <p class="footer-brand"><?= htmlspecialchars(\RuEdu\Engine\Config::get('site_name', '')) ?></p>
                <p><?= htmlspecialchars(\RuEdu\Engine\Config::get('site_description', '')) ?></p>
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
            <div class="footer-col">
                <h4>Разделы</h4>
                <ul>
                    <li><a href="<?= route('sveden') ?>">Сведения об ОО</a></li>
                    <li><a href="<?= route('news') ?>">Новости</a></li>
                    <li><a href="<?= route('schedule') ?>">Расписание</a></li>
                    <li><a href="<?= route('gallery') ?>">Галерея</a></li>
                    <li><a href="<?= route('contacts') ?>">Контакты</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(\RuEdu\Engine\Config::get('site_name', '')) ?>. Все права защищены.</p>
            <p class="footer-powered">Работает на <a href="https://github.com/RuEduCMS" target="_blank" rel="noopener">RuEduCMS</a></p>
        </div>
    </div>
</footer>
