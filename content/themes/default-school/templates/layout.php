<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= $meta ?? '' ?>
    <link rel="stylesheet" href="<?= $tpl->cssUrl() ?>">
    <?= $schema ?? '' ?>
</head>
<body<?= \RuEdu\Engine\AdminBar::bodyClass() ?>>
    <?= \RuEdu\Engine\AdminBar::render([
        'page' => $page ?? null,
        'article' => $article ?? null,
    ]) ?>
    <?php include __DIR__ . '/partials/header.php'; ?>
    <main class="main-content">
        <?= $content ?? '' ?>
    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>
    <?php include __DIR__ . '/partials/a11y.php'; ?>
    <?php include __DIR__ . '/partials/cookie-banner.php'; ?>
    <script src="<?= $tpl->asset('assets/js/theme.js') ?>"></script>
</body>
</html>
