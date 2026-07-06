<?php ob_start();
$page_title = $album['title'];
$page_breadcrumb = 'Галерея';
include __DIR__ . '/partials/page-header.php';
?>
<div class="container page-content">
    <?php if ($album['description']): ?><p data-animate><?= htmlspecialchars($album['description']) ?></p><?php endif; ?>
    <div class="gallery-grid">
        <?php foreach ($images as $i => $img): ?>
            <img src="<?= asset('uploads/' . $img['path']) ?>" alt="<?= htmlspecialchars($img['alt'] ?: $img['title']) ?>" loading="lazy" data-animate data-animate-delay="<?= min(($i % 6) + 1, 6) ?>">
        <?php endforeach; ?>
    </div>
    <a href="<?= route('gallery') ?>" class="btn btn-outline mt-4" data-animate>← Все альбомы</a>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
