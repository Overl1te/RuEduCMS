<?php ob_start();
$page_title = 'Новости';
$page_breadcrumb = 'Новости';
include __DIR__ . '/partials/page-header.php';
?>
<div class="container page-content">
    <div class="news-list">
        <?php foreach ($articles as $i => $article): ?>
            <article class="news-item" data-animate data-animate-delay="<?= min(($i % 6) + 1, 6) ?>">
                <h2><a href="<?= route('news/' . $article['slug']) ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
                <time class="news-date"><?= $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '' ?></time>
                <p><?= htmlspecialchars($article['excerpt'] ?? mb_substr(strip_tags($article['content'] ?? ''), 0, 200)) ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (empty($articles)): ?>
            <p data-animate>Новостей пока нет.</p>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
