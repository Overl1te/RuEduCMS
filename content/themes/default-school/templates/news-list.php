<?php ob_start(); ?>
<div class="container page-content">
    <h1>Новости</h1>
    <div class="news-list">
        <?php foreach ($articles as $article): ?>
            <article class="news-item">
                <h2><a href="<?= route('news/' . $article['slug']) ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
                <time class="news-date"><?= $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '' ?></time>
                <p><?= htmlspecialchars($article['excerpt'] ?? mb_substr(strip_tags($article['content'] ?? ''), 0, 200)) ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (empty($articles)): ?>
            <p>Новостей пока нет.</p>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
