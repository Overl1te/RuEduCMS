<?php ob_start(); ?>
<section class="hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="container hero-inner">
        <p class="hero-badge">Официальный сайт</p>
        <h1><?= htmlspecialchars($site_name ?? '') ?></h1>
        <p class="hero-subtitle">Добро пожаловать на официальный сайт образовательного учреждения</p>
        <div class="hero-links">
            <a href="<?= route('sveden') ?>" class="btn btn-primary">Сведения об ОО</a>
            <a href="<?= route('news') ?>" class="btn btn-outline">Новости</a>
            <a href="<?= route('contacts') ?>" class="btn btn-outline">Контакты</a>
        </div>
    </div>
</section>

<section class="section quick-links section-alt">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Быстрые ссылки</h2>
            <p class="section-subtitle">Основные разделы сайта</p>
        </div>
        <div class="links-grid">
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/page/informaciya')): ?>
            <a href="<?= route('page/informaciya') ?>" class="link-card">
                <span class="link-icon link-icon-docs" aria-hidden="true"></span>
                <span class="link-label">Информация</span>
            </a>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/schedule')): ?>
            <a href="<?= route('schedule') ?>" class="link-card">
                <span class="link-icon link-icon-schedule" aria-hidden="true"></span>
                <span class="link-label">Расписание</span>
            </a>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/page/priem-v-shkolu')): ?>
            <a href="<?= route('page/priem-v-shkolu') ?>" class="link-card">
                <span class="link-icon link-icon-staff" aria-hidden="true"></span>
                <span class="link-label">Приём в школу</span>
            </a>
            <?php endif; ?>
            <?php if (\RuEdu\Engine\Modules::isUrlEnabled('/gallery')): ?>
            <a href="<?= route('gallery') ?>" class="link-card">
                <span class="link-icon link-icon-gallery" aria-hidden="true"></span>
                <span class="link-label">Фотоальбомы</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section news-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Последние новости</h2>
            <p class="section-subtitle">Актуальные события и объявления</p>
        </div>
        <?php if (!empty($articles)): ?>
        <div class="news-list">
            <?php foreach (array_slice($articles, 0, 3) as $article): ?>
                <?php
                $fullText = $article['excerpt'] ?? strip_tags($article['content'] ?? '');
                $excerpt = mb_substr($fullText, 0, 200);
                ?>
                <article class="news-item">
                    <h2><a href="<?= route('news/' . $article['slug']) ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
                    <time class="news-date"><?= $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '' ?></time>
                    <?php if ($excerpt !== ''): ?>
                    <p><?= htmlspecialchars($excerpt) ?><?= mb_strlen($fullText) > 200 ? '…' : '' ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted">Новостей пока нет.</p>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="<?= route('news') ?>" class="btn btn-outline">Все новости →</a>
        </div>
    </div>
</section>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
