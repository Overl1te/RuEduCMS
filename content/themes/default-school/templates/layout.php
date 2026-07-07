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
<body class="is-loading" data-base-path="<?= htmlspecialchars(\RuEdu\Engine\Router::basePath()) ?>"<?= \RuEdu\Engine\AdminBar::bodyClass() ?>>
    <div class="page-loader" id="pageLoader" aria-hidden="true">
        <div class="page-loader__ring"></div>
    </div>
    <div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>
    <div class="site-canvas">
    <?= \RuEdu\Engine\AdminBar::render([
        'page' => $page ?? null,
        'article' => $article ?? null,
    ]) ?>
    <?php include __DIR__ . '/partials/header.php'; ?>
    <?php include __DIR__ . '/partials/a11y.php'; ?>
    <div class="page-layout<?= !empty($side_menu) ? ' has-sidebar' : '' ?>">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        <main class="main-content">
            <?= $content ?? '' ?>
        </main>
    </div>
    </div>
    <?php include __DIR__ . '/partials/footer.php'; ?>
    <?php include __DIR__ . '/partials/cookie-banner.php'; ?>
    <button type="button" class="back-to-top" id="backToTop" aria-label="Наверх" title="Наверх">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M18 15l-6-6-6 6"/></svg>
    </button>
    <div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>
    <script src="<?= $tpl->asset('assets/js/theme.js') ?>"></script>
    <?php if (!empty($site_flash_success)): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { showToast(<?= json_encode($site_flash_success, JSON_UNESCAPED_UNICODE) ?>, 'success'); });</script>
    <?php endif; ?>
    <?php if (!empty($site_flash_error)): ?>
    <script>document.addEventListener('DOMContentLoaded', function() { showToast(<?= json_encode($site_flash_error, JSON_UNESCAPED_UNICODE) ?>, 'error'); });</script>
    <?php endif; ?>
</body>
</html>
