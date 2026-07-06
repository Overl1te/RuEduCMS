<?php ob_start();
$fieldHtml = \RuEdu\Engine\FieldRenderer::renderEntity('home', [
    'site_name' => $site_name ?? null,
    'articles' => $articles ?? [],
]);
$blockHtml = $fieldHtml !== '' ? $fieldHtml : \RuEdu\Engine\BlockRenderer::renderHome([
    'site_name' => $site_name ?? null,
    'articles' => $articles ?? [],
]);
if ($blockHtml !== ''): ?>
    <?= $blockHtml ?>
<?php else: ?>
<section class="hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="hero-orb hero-orb--1" aria-hidden="true" data-parallax="0.15"></div>
    <div class="hero-orb hero-orb--2" aria-hidden="true" data-parallax="0.1"></div>
    <div class="hero-orb hero-orb--3" aria-hidden="true" data-parallax="0.2"></div>
    <div class="container hero-inner">
        <p class="hero-badge">Официальный сайт</p>
        <h1><?= htmlspecialchars($site_name ?? '') ?></h1>
        <p class="hero-subtitle">Добро пожаловать на официальный сайт образовательного учреждения — знания, традиции и будущее в одном месте</p>
        <div class="hero-links">
            <a href="<?= route('sveden') ?>" class="btn btn-primary">Сведения об ОО</a>
            <a href="<?= route('news') ?>" class="btn btn-outline">Новости</a>
            <a href="<?= route('contacts') ?>" class="btn btn-outline">Контакты</a>
        </div>
    </div>
</section>

<div class="stats-strip">
    <div class="stats-grid" data-animate>
        <div class="stat-item" data-animate data-animate-delay="1">
            <div class="stat-value" data-count="25">25+</div>
            <div class="stat-label">Лет опыта</div>
        </div>
        <div class="stat-item" data-animate data-animate-delay="2">
            <div class="stat-value" data-count="50">50+</div>
            <div class="stat-label">Педагогов</div>
        </div>
        <div class="stat-item" data-animate data-animate-delay="3">
            <div class="stat-value" data-count="500">500+</div>
            <div class="stat-label">Учеников</div>
        </div>
        <div class="stat-item" data-animate data-animate-delay="4">
            <div class="stat-value">∞</div>
            <div class="stat-label">Возможностей</div>
        </div>
    </div>
</div>

<section class="section quick-links section-alt">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-eyebrow">Навигация</span>
            <h2 class="section-title">Быстрые ссылки</h2>
            <p class="section-subtitle">Всё важное — в один клик</p>
        </div>
        <div class="links-grid">
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/page/informaciya')): ?>
            <a href="<?= route('page/informaciya') ?>" class="link-card" data-animate data-animate-delay="1">
                <span class="link-icon link-icon-docs" aria-hidden="true"></span>
                <span class="link-label">Информация</span>
            </a>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/schedule')): ?>
            <a href="<?= route('schedule') ?>" class="link-card" data-animate data-animate-delay="2">
                <span class="link-icon link-icon-schedule" aria-hidden="true"></span>
                <span class="link-label">Расписание</span>
            </a>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/page/priem-v-shkolu')): ?>
            <a href="<?= route('page/priem-v-shkolu') ?>" class="link-card" data-animate data-animate-delay="3">
                <span class="link-icon link-icon-staff" aria-hidden="true"></span>
                <span class="link-label">Приём в школу</span>
            </a>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/gallery')): ?>
            <a href="<?= route('gallery') ?>" class="link-card" data-animate data-animate-delay="4">
                <span class="link-icon link-icon-gallery" aria-hidden="true"></span>
                <span class="link-label">Фотоальбомы</span>
            </a>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/sveden')): ?>
            <a href="<?= route('sveden') ?>" class="link-card" data-animate data-animate-delay="5">
                <span class="link-icon link-icon-sveden" aria-hidden="true"></span>
                <span class="link-label">Сведения об ОО</span>
            </a>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/contacts')): ?>
            <a href="<?= route('contacts') ?>" class="link-card" data-animate data-animate-delay="6">
                <span class="link-icon link-icon-contacts" aria-hidden="true"></span>
                <span class="link-label">Контакты</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section news-section">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-eyebrow">Актуально</span>
            <h2 class="section-title">Последние новости</h2>
            <p class="section-subtitle">События, объявления и достижения</p>
        </div>
        <?php if (!empty($articles)): ?>
        <div class="news-grid">
            <?php foreach (array_slice($articles, 0, 3) as $i => $article): ?>
                <?php
                $fullText = $article['excerpt'] ?? strip_tags($article['content'] ?? '');
                $excerpt = mb_substr($fullText, 0, 160);
                $delay = min($i + 1, 6);
                ?>
                <article class="news-card" data-animate data-animate-delay="<?= $delay ?>">
                    <div class="news-card__body">
                        <time datetime="<?= $article['published_at'] ?? '' ?>"><?= $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '' ?></time>
                        <h3><a href="<?= route('news/' . $article['slug']) ?>"><?= htmlspecialchars($article['title']) ?></a></h3>
                        <?php if ($excerpt !== ''): ?>
                        <p><?= htmlspecialchars($excerpt) ?><?= mb_strlen($fullText) > 160 ? '…' : '' ?></p>
                        <?php endif; ?>
                        <a href="<?= route('news/' . $article['slug']) ?>" class="news-card__link">Читать далее →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted text-center" data-animate>Новостей пока нет.</p>
        <?php endif; ?>
        <div class="text-center mt-4" data-animate>
            <a href="<?= route('news') ?>" class="btn btn-primary">Все новости →</a>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container cta-band__inner" data-animate>
        <h2>Остались вопросы?</h2>
        <p>Свяжитесь с нами — мы всегда рады помочь родителям и ученикам</p>
        <div class="hero-links">
            <a href="<?= route('contacts') ?>" class="btn btn-primary">Написать нам</a>
            <a href="<?= route('sveden') ?>" class="btn btn-outline">Сведения об ОО</a>
        </div>
    </div>
</section>
<?php endif;
$content = ob_get_clean();
include __DIR__ . '/layout.php';
