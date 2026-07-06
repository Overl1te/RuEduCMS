<?php ob_start(); ?>
<div class="container page-content">
    <h1>Карта сайта</h1>
    <p class="sitemap-intro">
        Ниже перечислены все основные разделы и страницы сайта.
        Для поисковых систем доступна <a href="<?= route('sitemap.xml') ?>">XML-версия карты сайта</a>.
    </p>

    <div class="sitemap-tree-wrap">
        <?php $items = $sitemap_tree ?? []; include __DIR__ . '/partials/sitemap-tree.php'; ?>
    </div>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php'; ?>
