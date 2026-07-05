<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка <?= htmlspecialchars(\RuEdu\Engine\Lang::APP_NAME) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f2f5; color: #333; }
        .container { max-width: 700px; margin: 40px auto; padding: 0 20px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); padding: 32px; }
        h1 { font-size: 24px; margin-bottom: 8px; color: #1a56db; }
        .subtitle { color: #666; margin-bottom: 24px; }
        .steps { display: flex; gap: 8px; margin-bottom: 32px; }
        .step-item { flex: 1; text-align: center; padding: 8px; border-radius: 8px; font-size: 13px; background: #e5e7eb; color: #666; }
        .step-item.active { background: #1a56db; color: #fff; }
        .step-item.done { background: #10b981; color: #fff; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 4px; font-weight: 500; font-size: 14px; }
        input[type=text], input[type=email], input[type=password], input[type=url] {
            width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;
        }
        input:focus { outline: none; border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,.1); }
        .btn { display: inline-block; padding: 10px 24px; background: #1a56db; color: #fff; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; text-decoration: none; }
        .btn:hover { background: #1e40af; }
        .btn:disabled { background: #9ca3af; cursor: not-allowed; }
        .btn-success { background: #10b981; }
        .error { background: #fef2f2; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .check-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .check-ok { color: #10b981; }
        .check-fail { color: #dc2626; }
        .success-icon { font-size: 48px; text-align: center; margin-bottom: 16px; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1><?= htmlspecialchars(\RuEdu\Engine\Lang::APP_NAME) ?></h1>
        <p class="subtitle">Установка CMS для образовательных учреждений</p>

        <div class="steps">
            <?php for ($i = 1; $i <= 4; $i++): ?>
                <div class="step-item <?= $i === $step ? 'active' : ($i < $step ? 'done' : '') ?>">
                    <?= $i ?>
                </div>
            <?php endfor; ?>
        </div>

        <?php if ($errors): ?>
            <div class="error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <h2 style="margin-bottom:16px;font-size:18px;">Шаг 1: Проверка окружения</h2>
            <?php foreach ($requirements as $check): ?>
                <div class="check-item">
                    <span><?= htmlspecialchars($check['label']) ?></span>
                    <span class="<?= $check['ok'] ? 'check-ok' : 'check-fail' ?>">
                        <?= $check['ok'] ? '✓' : '✗' ?> <?= htmlspecialchars($check['value']) ?>
                    </span>
                </div>
            <?php endforeach; ?>
            <div style="margin-top:24px;">
                <?php if ($allOk): ?>
                    <a href="?step=2" class="btn">Продолжить →</a>
                <?php else: ?>
                    <button class="btn" disabled>Исправьте ошибки для продолжения</button>
                <?php endif; ?>
            </div>

        <?php elseif ($step === 2): ?>
            <h2 style="margin-bottom:16px;font-size:18px;">Шаг 2: Подключение к базе данных</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Хост БД</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Имя базы данных</label>
                    <input type="text" name="db_name" required placeholder="база_данных">
                </div>
                <div class="form-group">
                    <label>Пользователь</label>
                    <input type="text" name="db_user" required>
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="db_pass">
                </div>
                <div class="form-group">
                    <label>Префикс таблиц</label>
                    <input type="text" name="db_prefix" value="rc_">
                </div>
                <button type="submit" class="btn">Проверить и продолжить →</button>
            </form>

        <?php elseif ($step === 3): ?>
            <h2 style="margin-bottom:16px;font-size:18px;">Шаг 3: Создание таблиц</h2>
            <p style="margin-bottom:16px;color:#666;">Нажмите кнопку для создания таблиц в базе данных.</p>
            <form method="POST">
                <button type="submit" class="btn">Создать таблицы →</button>
            </form>

        <?php elseif ($step === 4): ?>
            <h2 style="margin-bottom:16px;font-size:18px;">Шаг 4: Настройка сайта</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Название сайта</label>
                    <input type="text" name="site_name" required placeholder="МБОУ СОШ №1">
                </div>
                <div class="form-group">
                    <label>URL сайта</label>
                    <input type="url" name="site_url" required placeholder="https://школа.образование.рф"
                           value="<?= htmlspecialchars(\RuEdu\Engine\Router::detectSiteUrl()) ?>">
                </div>
                <hr style="margin:20px 0;border:none;border-top:1px solid #e5e7eb;">
                <div class="form-group">
                    <label>Имя администратора</label>
                    <input type="text" name="admin_name" required>
                </div>
                <div class="form-group">
                    <label>Логин администратора</label>
                    <input type="text" name="admin_login" required pattern="[a-zA-Z0-9._-]+" placeholder="администратор">
                    <small style="color:#6b7280;">Латиница, цифры, точка, дефис, подчёркивание</small>
                </div>
                <div class="form-group">
                    <label>Email администратора</label>
                    <input type="email" name="admin_email" required>
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="admin_password" required minlength="6">
                </div>
                <button type="submit" class="btn">Завершить установку →</button>
            </form>

        <?php endif; ?>
    </div>
</div>
</body>
</html>
