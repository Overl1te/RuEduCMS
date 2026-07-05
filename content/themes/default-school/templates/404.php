<?php ob_start(); ?>
<section class="error-page">
    <div class="container">
        <div class="error-page-inner">
            <p class="error-code" aria-hidden="true">404</p>
            <h1>Страница не найдена</h1>
            <p class="error-text">Запрашиваемая страница не существует, была удалена или адрес введён с ошибкой.</p>
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
