<?php ob_start();
$page_title = $article['title'];
$page_breadcrumb = 'Новости';
include __DIR__ . '/partials/page-header.php';
?>
<div class="ed-container ed-page-content">
    <article class="ed-article">
        <time class="ed-article__date"><?= $article['published_at'] ? date('d.m.Y', strtotime($article['published_at'])) : '' ?></time>
        <?php if ($article['category_name'] ?? false): ?>
            <span class="ed-badge"><?= htmlspecialchars($article['category_name']) ?></span>
        <?php endif; ?>
        <div class="ed-content-body">
            <?= $article['content'] ?>
        </div>
    </article>
    <a href="<?= route('news') ?>" class="ed-btn ed-btn--outline ed-mt-4">← Все новости</a>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
