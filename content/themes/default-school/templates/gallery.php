<?php ob_start(); ?>
<div class="container page-content">
    <h1>Фотогалерея</h1>
    <div class="gallery-grid">
        <?php foreach ($albums as $album): ?>
            <a href="<?= route('gallery/' . $album['slug']) ?>" class="link-card">
                <span class="link-icon">📷</span>
                <span><?= htmlspecialchars($album['title']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php if (empty($albums)): ?><p>Альбомы пока не созданы.</p><?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
