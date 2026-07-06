<?php ob_start();
$page_title = $article['title'];
$page_breadcrumb = 'Новости';
include __DIR__ . '/partials/page-header.php';
?>
<div class="container page-content">
    <article data-animate>
        <time class="news-date"><?= $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '' ?></time>
        <?php if ($article['category_name'] ?? false): ?>
            <span class="badge"><?= htmlspecialchars($article['category_name']) ?></span>
        <?php endif; ?>
        <div class="content-body">
            <?= $article['content'] ?>
        </div>
    </article>
    <a href="<?= route('news') ?>" class="btn btn-outline mt-4" data-animate>← Все новости</a>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
