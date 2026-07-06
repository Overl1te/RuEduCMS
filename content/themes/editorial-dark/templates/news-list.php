<?php ob_start();
$page_title = 'Новости';
$page_breadcrumb = 'Новости';
include __DIR__ . '/partials/page-header.php';
?>
<div class="ed-container ed-page-content">
    <div class="ed-news-feed ed-news-feed--list">
        <?php foreach ($articles as $article): ?>
            <article class="ed-news-item">
                <h2><a href="<?= route('news/' . $article['slug']) ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
                <time><?= $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '' ?></time>
                <p><?= htmlspecialchars($article['excerpt'] ?? mb_substr(strip_tags($article['content'] ?? ''), 0, 200)) ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (empty($articles)): ?>
            <p class="ed-muted">Новостей пока нет.</p>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
