<?php ob_start(); ?>
<section class="hero">
    <div class="container">
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
        <h2 class="section-title">Последние новости</h2>
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

<section class="section quick-links">
    <div class="container">
        <div class="links-grid">
            <a href="<?= route('staff') ?>" class="link-card"><span class="link-icon">👨‍🏫</span><span>Педагогический состав</span></a>
            <a href="<?= route('schedule') ?>" class="link-card"><span class="link-icon">📅</span><span>Расписание</span></a>
            <a href="<?= route('documents') ?>" class="link-card"><span class="link-icon">📄</span><span>Документы</span></a>
            <a href="<?= route('gallery') ?>" class="link-card"><span class="link-icon">📷</span><span>Галерея</span></a>
        </div>
    </div>
</section>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
