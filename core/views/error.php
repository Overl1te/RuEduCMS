<?php
/** @var int $code */
/** @var string $title */
/** @var string $message */
/** @var string $siteName */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — <?= htmlspecialchars($siteName) ?></title>
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
        .error-wrap { width: 100%; max-width: 520px; text-align: center; }
        .error-code {
            font-size: clamp(4rem, 12vw, 6rem);
            font-weight: 700;
            color: #c3c4c7;
            line-height: 1;
            margin-bottom: 8px;
        }
        .error-box {
            background: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 32px 28px;
        }
        .error-box h1 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .error-box p {
            font-size: 15px;
            color: #50575e;
        }
    </style>
</head>
<body>
    <div class="error-wrap">
        <p class="error-code" aria-hidden="true"><?= (int) $code ?></p>
        <div class="error-box">
            <h1><?= htmlspecialchars($title) ?></h1>
            <p><?= htmlspecialchars($message) ?></p>
        </div>
    </div>
</body>
</html>
