<?php ob_start();
$page_title = $page['title'];
include __DIR__ . '/partials/page-header.php';
?>
<div class="ed-container ed-page-content">
    <div class="ed-content-body">
        <?= $page['content'] ?>
    </div>
    <?php if (($page['slug'] ?? '') === 'informaciya'): ?>
        <div class="ed-map-section">
            <?php include __DIR__ . '/partials/yandex-map.php'; ?>
        </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
