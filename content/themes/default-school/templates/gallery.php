<?php ob_start();
$page_title = 'Фотогалерея';
$page_breadcrumb = 'Галерея';
include __DIR__ . '/partials/page-header.php';
?>
<div class="container page-content">
    <div class="gallery-grid">
        <?php foreach ($albums as $i => $album): ?>
            <a href="<?= route('gallery/' . $album['slug']) ?>" class="gallery-album-card" data-animate data-animate-delay="<?= min(($i % 6) + 1, 6) ?>">
                <div class="gallery-album-card__img">
                    <?php if (!empty($album['cover'])): ?>
                        <img src="<?= asset('uploads/' . $album['cover']) ?>" alt="<?= htmlspecialchars($album['title']) ?>">
                    <?php else: ?>
                        <span aria-hidden="true">📷</span>
                    <?php endif; ?>
                </div>
                <div class="gallery-album-card__body"><?= htmlspecialchars($album['title']) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
    <?php if (empty($albums)): ?><p data-animate>Альбомы пока не созданы.</p><?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
