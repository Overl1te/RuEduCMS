<?php ob_start(); ?>
<div class="container page-content">
    <h1>Страница не найдена</h1>
    <p>Страница не найдена.</p>
    <a href="<?= route('') ?>" class="btn btn-primary">На главную</a>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
