<?php ob_start();
$page_title = 'Фотогалерея';
$page_breadcrumb = 'Галерея';
include __DIR__ . '/partials/page-header.php';
?>
<div class="ed-container ed-page-content">
    <div class="ed-gallery-grid">
        <?php foreach ($albums as $album): ?>
            <a href="<?= route('gallery/' . $album['slug']) ?>" class="ed-gallery-album">
                <div class="ed-gallery-album__img">
                    <?php if (!empty($album['cover'])): ?>
                        <img src="<?= asset('uploads/' . $album['cover']) ?>" alt="<?= htmlspecialchars($album['title']) ?>">
                    <?php else: ?>
                        <span aria-hidden="true">—</span>
                    <?php endif; ?>
                </div>
                <div class="ed-gallery-album__title"><?= htmlspecialchars($album['title']) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
    <?php if (empty($albums)): ?><p class="ed-muted">Альбомы пока не созданы.</p><?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
