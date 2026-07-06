<?php ob_start();
$blockHtml = \RuEdu\Engine\BlockRenderer::renderHome([
    'site_name' => $site_name ?? null,
    'articles' => $articles ?? [],
]);
if ($blockHtml !== ''): ?>
    <?= $blockHtml ?>
<?php else: ?>
<section class="ed-hero">
    <div class="ed-container ed-hero__inner">
        <p class="ed-hero__label">Официальный сайт</p>
        <h1 class="ed-hero__title"><?= htmlspecialchars($site_name ?? '') ?></h1>
        <p class="ed-hero__subtitle">Добро пожаловать на официальный сайт образовательного учреждения — знания, традиции и будущее в одном месте</p>
        <div class="ed-hero__actions">
            <a href="<?= route('sveden') ?>" class="ed-btn ed-btn--primary">Сведения об ОО</a>
            <a href="<?= route('news') ?>" class="ed-btn ed-btn--outline">Новости</a>
            <a href="<?= route('contacts') ?>" class="ed-btn ed-btn--outline">Контакты</a>
        </div>
    </div>
    <hr class="ed-rule ed-rule--wide">
</section>

<section class="ed-section ed-stats">
    <div class="ed-container">
        <div class="ed-stats__grid">
            <div class="ed-stat"><span class="ed-stat__value">25+</span><span class="ed-stat__label">Лет опыта</span></div>
            <div class="ed-stat"><span class="ed-stat__value">50+</span><span class="ed-stat__label">Педагогов</span></div>
            <div class="ed-stat"><span class="ed-stat__value">500+</span><span class="ed-stat__label">Учеников</span></div>
            <div class="ed-stat"><span class="ed-stat__value">∞</span><span class="ed-stat__label">Возможностей</span></div>
        </div>
    </div>
</section>

<section class="ed-section ed-section--alt">
    <div class="ed-container">
        <header class="ed-section__header">
            <span class="ed-eyebrow">Навигация</span>
            <h2 class="ed-section__title">Быстрые ссылки</h2>
        </header>
        <ol class="ed-link-list">
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/page/informaciya')): ?>
            <li><a href="<?= route('page/informaciya') ?>"><span class="ed-link-list__num">01</span> Информация</a></li>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/schedule')): ?>
            <li><a href="<?= route('schedule') ?>"><span class="ed-link-list__num">02</span> Расписание</a></li>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/page/priem-v-shkolu')): ?>
            <li><a href="<?= route('page/priem-v-shkolu') ?>"><span class="ed-link-list__num">03</span> Приём в школу</a></li>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/gallery')): ?>
            <li><a href="<?= route('gallery') ?>"><span class="ed-link-list__num">04</span> Фотоальбомы</a></li>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/sveden')): ?>
            <li><a href="<?= route('sveden') ?>"><span class="ed-link-list__num">05</span> Сведения об ОО</a></li>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/contacts')): ?>
            <li><a href="<?= route('contacts') ?>"><span class="ed-link-list__num">06</span> Контакты</a></li>
            <?php endif; ?>
        </ol>
    </div>
</section>

<section class="ed-section">
    <div class="ed-container">
        <header class="ed-section__header">
            <span class="ed-eyebrow">Актуально</span>
            <h2 class="ed-section__title">Последние новости</h2>
        </header>
        <?php if (!empty($articles)): ?>
        <div class="ed-news-feed">
            <?php foreach (array_slice($articles, 0, 3) as $article): ?>
                <?php
                $fullText = $article['excerpt'] ?? strip_tags($article['content'] ?? '');
                $excerpt = mb_substr($fullText, 0, 160);
                ?>
                <article class="ed-news-item">
                    <time datetime="<?= $article['published_at'] ?? '' ?>"><?= $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '' ?></time>
                    <h3><a href="<?= route('news/' . $article['slug']) ?>"><?= htmlspecialchars($article['title']) ?></a></h3>
                    <?php if ($excerpt !== ''): ?>
                    <p><?= htmlspecialchars($excerpt) ?><?= mb_strlen($fullText) > 160 ? '…' : '' ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="ed-muted">Новостей пока нет.</p>
        <?php endif; ?>
        <p class="ed-section__footer">
            <a href="<?= route('news') ?>" class="ed-btn ed-btn--primary">Все новости</a>
        </p>
    </div>
</section>

<section class="ed-cta">
    <div class="ed-container ed-cta__inner">
        <h2>Остались вопросы?</h2>
        <p>Свяжитесь с нами — мы всегда рады помочь родителям и ученикам</p>
        <div class="ed-hero__actions">
            <a href="<?= route('contacts') ?>" class="ed-btn ed-btn--primary">Написать нам</a>
            <a href="<?= route('sveden') ?>" class="ed-btn ed-btn--outline">Сведения об ОО</a>
        </div>
    </div>
</section>
<?php endif;
$content = ob_get_clean();
include __DIR__ . '/layout.php';
