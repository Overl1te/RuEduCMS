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

<?php if (!empty($articles)): ?>
<section class="section news-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Последние новости</h2>
            <p class="section-subtitle">Актуальные события и объявления</p>
        </div>
        <div class="news-grid">
            <?php foreach ($articles as $article): ?>
                <article class="news-card">
                    <h3><a href="<?= route('news/' . $article['slug']) ?>"><?= htmlspecialchars($article['title']) ?></a></h3>
                    <time><?= $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '' ?></time>
                    <p><?= htmlspecialchars(mb_substr($article['excerpt'] ?? strip_tags($article['content'] ?? ''), 0, 150)) ?>...</p>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= route('news') ?>" class="btn btn-outline">Все новости →</a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section quick-links section-alt">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Быстрые ссылки</h2>
            <p class="section-subtitle">Основные разделы сайта</p>
        </div>
        <div class="links-grid">
            <a href="<?= route('staff') ?>" class="link-card">
                <span class="link-icon link-icon-staff" aria-hidden="true"></span>
                <span class="link-label">Педагогический состав</span>
            </a>
            <a href="<?= route('schedule') ?>" class="link-card">
                <span class="link-icon link-icon-schedule" aria-hidden="true"></span>
                <span class="link-label">Расписание</span>
            </a>
            <a href="<?= route('documents') ?>" class="link-card">
                <span class="link-icon link-icon-docs" aria-hidden="true"></span>
                <span class="link-label">Документы</span>
            </a>
            <a href="<?= route('gallery') ?>" class="link-card">
                <span class="link-icon link-icon-gallery" aria-hidden="true"></span>
                <span class="link-label">Галерея</span>
            </a>
        </div>
    </div>
</section>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
