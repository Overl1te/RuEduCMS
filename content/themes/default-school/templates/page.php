<?php ob_start(); ?>
<div class="container page-content">
    <h1><?= htmlspecialchars($page['title']) ?></h1>
    <div class="content-body">
        <?= $page['content'] ?>
    </div>
    <?php if (($page['slug'] ?? '') === 'informaciya'): ?>
        <div class="map-section">
            <?php include __DIR__ . '/partials/yandex-map.php'; ?>
        </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
