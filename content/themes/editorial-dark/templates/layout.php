<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= $meta ?? '' ?>
    <?= \RuEdu\Engine\SEO::headLinks() ?>
    <?= \RuEdu\Engine\ThemeCustomizer::renderFontLinks() ?>
    <link rel="stylesheet" href="<?= $tpl->cssUrl() ?>">
    <?= \RuEdu\Engine\ThemeCustomizer::renderStyleTag() ?>
    <?= $schema ?? '' ?>
</head>
<body class="ed-body" data-base-path="<?= htmlspecialchars(\RuEdu\Engine\Router::basePath()) ?>"<?= \RuEdu\Engine\AdminBar::bodyClass() ?>>
    <div class="ed-wrap">
        <?= \RuEdu\Engine\AdminBar::render([
            'page' => $page ?? null,
            'article' => $article ?? null,
        ]) ?>
        <?php include __DIR__ . '/partials/header.php'; ?>
        <?php include __DIR__ . '/partials/a11y.php'; ?>
        <main class="ed-main">
            <?= $content ?? '' ?>
        </main>
        <?php include __DIR__ . '/partials/footer.php'; ?>
    </div>
    <?php include __DIR__ . '/partials/cookie-banner.php'; ?>
    <div id="toastContainer" class="ed-toast-container" aria-live="polite" aria-atomic="true"></div>
    <script src="<?= $tpl->asset('assets/js/theme.js') ?>"></script>
    <?php if (!empty($site_flash_success)): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { showToast(<?= json_encode($site_flash_success, JSON_UNESCAPED_UNICODE) ?>, 'success'); });</script>
    <?php endif; ?>
    <?php if (!empty($site_flash_error)): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { showToast(<?= json_encode($site_flash_error, JSON_UNESCAPED_UNICODE) ?>, 'error'); });</script>
    <?php endif; ?>
</body>
</html>
