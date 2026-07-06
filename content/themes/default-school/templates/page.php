<?php ob_start();
$page_title = $page['title'];
include __DIR__ . '/partials/page-header.php';
?>
<div class="container page-content">
    <div class="content-body" data-animate>
        <?= $page['content'] ?>
    </div>
    <?php if (($page['slug'] ?? '') === 'informaciya'): ?>
        <div class="map-section" data-animate>
            <?php include __DIR__ . '/partials/yandex-map.php'; ?>
        </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
