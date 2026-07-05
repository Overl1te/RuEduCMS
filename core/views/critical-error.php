<?php
/** @var bool $showDetails */
/** @var string $siteName */
/** @var \Throwable $e */
/** @var bool $isAdminRequest */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Критическая ошибка — <?= htmlspecialchars($siteName) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background: #f0f0f1;
            color: #1d2327;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            line-height: 1.5;
        }
        .error-wrap { width: 100%; max-width: 680px; }
        .error-box {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-left: 4px solid #d63638;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 24px 28px;
            margin-bottom: 16px;
        }
        .error-box h1 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1d2327;
        }
        .error-box p {
            font-size: 15px;
            color: #50575e;
            margin-bottom: 8px;
        }
        .error-box p:last-child { margin-bottom: 0; }
        .error-debug {
            background: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 20px 24px;
            margin-bottom: 16px;
        }
        .error-debug h2 {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #d63638;
            margin-bottom: 12px;
        }
        .error-message {
            font-size: 15px;
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 8px;
            word-break: break-word;
        }
        .error-location {
            font-size: 13px;
            color: #646970;
            margin-bottom: 16px;
            word-break: break-all;
        }
        .error-trace {
            background: #f6f7f7;
            border: 1px solid #dcdcde;
            padding: 12px 16px;
            font-family: Consolas, Monaco, monospace;
            font-size: 12px;
            line-height: 1.6;
            color: #1d2327;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 400px;
            overflow-y: auto;
        }
        .error-note {
            font-size: 13px;
            color: #646970;
            padding: 0 4px;
        }
        .error-links {
            margin-top: 16px;
            font-size: 14px;
        }
        .error-links a {
            color: #2271b1;
            text-decoration: none;
        }
        .error-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="error-wrap">
        <div class="error-box">
            <h1>На сайте возникла критическая ошибка</h1>
            <?php if ($showDetails): ?>
                <p>Вы видите подробности, потому что авторизованы в панели управления.</p>
            <?php else: ?>
                <p>На сайте возникла критическая ошибка. Пожалуйста, повторите попытку позже. Если проблема сохраняется, обратитесь к администратору сайта.</p>
            <?php endif; ?>
        </div>

        <?php if ($showDetails): ?>
            <div class="error-debug">
                <h2>Техническая информация</h2>
                <div class="error-message"><?= htmlspecialchars(get_class($e) . ': ' . $e->getMessage()) ?></div>
                <div class="error-location">в файле <?= htmlspecialchars($e->getFile()) ?> на строке <?= (int) $e->getLine() ?></div>
                <div class="error-trace"><?= htmlspecialchars($e->getTraceAsString()) ?></div>
            </div>
            <p class="error-note">Подробности также записаны в журнал ошибок (папка хранилища)</p>
        <?php endif; ?>

        <div class="error-links">
            <?php if ($isAdminRequest): ?>
                <a href="<?= htmlspecialchars(\RuEdu\Engine\Router::path('admin')) ?>">← В панель управления</a>
                &nbsp;·&nbsp;
            <?php endif; ?>
            <a href="<?= htmlspecialchars(\RuEdu\Engine\Router::path('')) ?>">На главную сайта</a>
        </div>
    </div>
</body>
</html>
