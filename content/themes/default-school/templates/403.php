<?php ob_start(); ?>
<section class="error-page">
    <div class="container">
        <div class="error-page-inner">
            <p class="error-code" aria-hidden="true">403</p>
            <h1>Доступ запрещён</h1>
            <p class="error-text">У вас нет прав для просмотра этой страницы, либо запрошенный ресурс недоступен.</p>
            <div class="error-actions">
                <a href="<?= route('') ?>" class="btn btn-primary">На главную</a>
                <a href="<?= route('news') ?>" class="btn btn-outline">Новости</a>
                <a href="<?= route('contacts') ?>" class="btn btn-outline">Контакты</a>
            </div>
        </div>
    </div>
</section>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
