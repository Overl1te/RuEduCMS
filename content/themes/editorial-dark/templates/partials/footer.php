<?php $siteName = \RuEdu\Engine\Config::get('site_name', ''); ?>
<footer class="ed-footer">
    <div class="ed-container ed-footer__inner">
        <div class="ed-footer__top">
            <div class="ed-footer__brand">
                <p class="ed-footer__name"><?= htmlspecialchars($siteName) ?></p>
                <?php if ($desc = \RuEdu\Engine\Config::get('site_description', '')): ?>
                    <p class="ed-footer__tagline"><?= htmlspecialchars($desc) ?></p>
                <?php endif; ?>
            </div>
            <nav class="ed-footer__links" aria-label="Разделы">
                <a href="<?= route('sveden') ?>">Сведения об ОО</a>
                <a href="<?= route('news') ?>">Новости</a>
                <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/schedule')): ?>
                <a href="<?= route('schedule') ?>">Расписание</a>
                <?php endif; ?>
                <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/gallery')): ?>
                <a href="<?= route('gallery') ?>">Галерея</a>
                <?php endif; ?>
                <a href="<?= route('contacts') ?>">Контакты</a>
                <a href="<?= route('sitemap') ?>">Карта сайта</a>
            </nav>
        </div>
        <div class="ed-footer__bottom">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?></p>
            <p><a href="https://github.com/RuEduCMS" target="_blank" rel="noopener">RuEduCMS</a></p>
        </div>
    </div>
</footer>
