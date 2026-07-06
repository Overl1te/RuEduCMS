<?php ob_start();
$page_title = $album['title'];
$page_breadcrumb = 'Галерея';
include __DIR__ . '/partials/page-header.php';
?>
<div class="ed-container ed-page-content">
    <?php if ($album['description']): ?><p><?= htmlspecialchars($album['description']) ?></p><?php endif; ?>
    <div class="ed-gallery-grid ed-gallery-grid--images">
        <?php foreach ($images as $img): ?>
            <img src="<?= asset('uploads/' . $img['path']) ?>" alt="<?= htmlspecialchars($img['alt'] ?: $img['title']) ?>" loading="lazy">
        <?php endforeach; ?>
    </div>
    <a href="<?= route('gallery') ?>" class="ed-btn ed-btn--outline ed-mt-4">← Все альбомы</a>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
