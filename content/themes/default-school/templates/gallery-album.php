<?php ob_start(); ?>
<div class="container page-content">
    <h1><?= htmlspecialchars($album['title']) ?></h1>
    <?php if ($album['description']): ?><p><?= htmlspecialchars($album['description']) ?></p><?php endif; ?>
    <div class="gallery-grid">
        <?php foreach ($images as $img): ?>
            <img src="<?= asset('uploads/' . $img['path']) ?>" alt="<?= htmlspecialchars($img['alt'] ?: $img['title']) ?>" loading="lazy">
        <?php endforeach; ?>
    </div>
    <a href="<?= route('gallery') ?>" class="btn btn-outline mt-4">← Все альбомы</a>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
