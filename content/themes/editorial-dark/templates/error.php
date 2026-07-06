<?php ob_start(); ?>
<section class="ed-error">
    <div class="ed-container">
        <p class="ed-error__code" aria-hidden="true"><?= (int) ($error_code ?? 500) ?></p>
        <h1><?= htmlspecialchars($error_title ?? 'Ошибка') ?></h1>
        <p class="ed-error__text"><?= htmlspecialchars($error_message ?? '') ?></p>
        <div class="ed-error__actions">
            <a href="<?= route('') ?>" class="ed-btn ed-btn--primary">На главную</a>
            <a href="<?= route('news') ?>" class="ed-btn ed-btn--outline">Новости</a>
            <a href="<?= route('contacts') ?>" class="ed-btn ed-btn--outline">Контакты</a>
        </div>
    </div>
</section>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
