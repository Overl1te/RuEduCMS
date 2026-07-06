<?php ob_start(); ?>
<section class="error-page">
    <div class="container">
        <div class="error-page-inner">
            <p class="error-code" aria-hidden="true"><?= (int) ($error_code ?? 500) ?></p>
            <h1><?= htmlspecialchars($error_title ?? 'Ошибка') ?></h1>
            <p class="error-text"><?= htmlspecialchars($error_message ?? '') ?></p>
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
