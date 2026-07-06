<?php ob_start();
$page_title = 'Карта сайта';
$page_breadcrumb = 'Карта сайта';
include __DIR__ . '/partials/page-header.php';
?>
<div class="ed-container ed-page-content">
    <p class="ed-sitemap-intro">
        Ниже перечислены все основные разделы и страницы сайта.
        Для поисковых систем доступна <a href="<?= route('sitemap.xml') ?>">XML-версия карты сайта</a>.
    </p>
    <div class="ed-sitemap-wrap">
        <?php $items = $sitemap_tree ?? []; include __DIR__ . '/partials/sitemap-tree.php'; ?>
    </div>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
