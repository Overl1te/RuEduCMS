<?php ob_start(); ?>
<section class="ed-error">
    <div class="ed-container">
        <p class="ed-error__code" aria-hidden="true">404</p>
        <h1>Страница не найдена</h1>
        <p class="ed-error__text">Запрашиваемая страница не существует, была удалена или адрес введён с ошибкой.</p>
        <div class="ed-error__actions">
            <a href="<?= route('') ?>" class="ed-btn ed-btn--primary">На главную</a>
            <a href="<?= route('news') ?>" class="ed-btn ed-btn--outline">Новости</a>
            <a href="<?= route('contacts') ?>" class="ed-btn ed-btn--outline">Контакты</a>
        </div>
    </div>
</section>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
