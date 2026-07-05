<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h4><?= htmlspecialchars(\RuEdu\Engine\Config::get('site_name', '')) ?></h4>
                <p><?= htmlspecialchars(\RuEdu\Engine\Config::get('site_description', '')) ?></p>
            </div>
            <div class="footer-col">
                <h4>Контакты</h4>
                <?php if ($phone = \RuEdu\Engine\Config::get('contact_phone', '')): ?>
                    <p>Тел: <?= htmlspecialchars($phone) ?></p>
                <?php endif; ?>
                <?php if ($addr = \RuEdu\Engine\Config::get('contact_address', '')): ?>
                    <p><?= htmlspecialchars($addr) ?></p>
                <?php endif; ?>
            </div>
            <div class="footer-col">
                <h4>Разделы</h4>
                <ul>
                    <li><a href="<?= route('sveden') ?>">Сведения об ОО</a></li>
                    <li><a href="<?= route('news') ?>">Новости</a></li>
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
