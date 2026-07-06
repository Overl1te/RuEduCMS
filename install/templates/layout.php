<?php
$stepLabels = ['Окружение', 'База данных', 'Таблицы', 'Настройка'];
$progressPercent = $step > 1 ? (($step - 1) / 3) * 76 : 0;
$assetBase = \RuEdu\Engine\Router::path('install/assets/');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка <?= htmlspecialchars(\RuEdu\Engine\Lang::APP_NAME) ?></title>
    <link rel="icon" href="<?= htmlspecialchars(\RuEdu\Engine\SiteBranding::faviconUrl()) ?>" type="image/png">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase) ?>install.css">
</head>
<body>
<div class="install-bg"></div>

<div class="container">
    <div class="card">
        <header class="install-header">
            <div class="install-logo" aria-hidden="true">R</div>
            <h1><?= htmlspecialchars(\RuEdu\Engine\Lang::APP_NAME) ?></h1>
            <p class="subtitle">Установка CMS для образовательных учреждений</p>
        </header>

        <nav class="stepper" aria-label="Шаги установки">
            <div class="stepper-progress" style="width: <?= (int) $progressPercent ?>%"></div>
            <?php for ($i = 1; $i <= 4; $i++):
                $state = $i === $step ? 'active' : ($i < $step ? 'done' : '');
            ?>
                <div class="step <?= $state ?>">
                    <div class="step-circle">
                        <span class="step-num"><?= $i ?></span>
                    </div>
                    <span class="step-label"><?= $stepLabels[$i - 1] ?></span>
                </div>
            <?php endfor; ?>
        </nav>

        <?php if ($errors): ?>
            <div class="error" role="alert"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
        <?php endif; ?>

        <main class="step-content">
        <?php if ($step === 1): ?>
            <h2 class="step-title">Шаг 1: Проверка окружения</h2>
            <p class="step-desc">Убедимся, что сервер готов к работе с RuEduCMS.</p>
            <ul class="checklist">
                <?php $idx = 0; foreach ($requirements as $check): ?>
                    <li class="check-item" style="animation-delay: <?= $idx * 0.06 ?>s">
                        <span class="check-item-label"><?= htmlspecialchars($check['label']) ?></span>
                        <span class="check-badge <?= $check['ok'] ? 'check-ok' : 'check-fail' ?>">
                            <span class="check-icon"><?= $check['ok'] ? '✓' : '✗' ?></span>
                            <?= htmlspecialchars($check['value']) ?>
                        </span>
                    </li>
                <?php $idx++; endforeach; ?>
            </ul>
            <div class="actions">
                <?php if ($allOk): ?>
                    <a href="?step=2" class="btn" data-install-link data-messages='["Проверка окружения…","Всё в порядке!"]'>
                        Продолжить <span class="btn-arrow">→</span>
                    </a>
                <?php else: ?>
                    <button class="btn" disabled>Исправьте ошибки для продолжения</button>
                <?php endif; ?>
            </div>

        <?php elseif ($step === 2): ?>
            <h2 class="step-title">Шаг 2: Подключение к базе данных</h2>
            <p class="step-desc">Укажите параметры доступа к MySQL или MariaDB.</p>
            <form method="POST" data-install-check data-messages='["Проверка подключения…","Установка соединения…","Готово!"]'>
                <div class="form-group">
                    <label for="db_host">Хост БД</label>
                    <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($installConfig['db_host'] ?? 'localhost') ?>" required>
                </div>
                <div class="form-group">
                    <label for="db_name">Имя базы данных</label>
                    <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($installConfig['db_name'] ?? '') ?>" required placeholder="база_данных">
                </div>
                <div class="form-group">
                    <label for="db_user">Пользователь</label>
                    <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($installConfig['db_user'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="db_pass">Пароль</label>
                    <input type="password" id="db_pass" name="db_pass" value="<?= htmlspecialchars($installConfig['db_pass'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="db_prefix">Префикс таблиц</label>
                    <input type="text" id="db_prefix" name="db_prefix" value="<?= htmlspecialchars($installConfig['db_prefix'] ?? 'rc_') ?>">
                </div>
                <div class="actions">
                    <button type="submit" class="btn">
                        Проверить и продолжить <span class="btn-arrow">→</span>
                    </button>
                </div>
            </form>

        <?php elseif ($step === 3): ?>
            <h2 class="step-title">Шаг 3: Создание таблиц</h2>
            <p class="step-desc">Нажмите кнопку для создания структуры базы данных.</p>
            <form method="POST" data-install-loading data-messages='["Подключение к базе данных…","Создание таблиц…","Настройка индексов…","Готово!"]'>
                <div class="actions">
                    <button type="submit" class="btn">
                        Создать таблицы <span class="btn-arrow">→</span>
                    </button>
                </div>
            </form>

        <?php elseif ($step === 4): ?>
            <h2 class="step-title">Шаг 4: Настройка сайта</h2>
            <p class="step-desc">Задайте основные параметры и учётную запись администратора.</p>
            <form method="POST" data-install-loading data-messages='["Сохранение конфигурации…","Создание администратора…","Подготовка панели…","Завершение…"]'>
                <div class="form-group">
                    <label for="site_name">Название сайта</label>
                    <input type="text" id="site_name" name="site_name" required placeholder="МБОУ СОШ №1"
                           value="<?= htmlspecialchars($installConfig['site_name'] ?? 'Мой сайт') ?>">
                </div>
                <div class="form-group">
                    <label for="site_url">URL сайта</label>
                    <input type="url" id="site_url" name="site_url" required placeholder="https://школа.образование.рф"
                           value="<?= htmlspecialchars($installConfig['site_url'] !== '' ? $installConfig['site_url'] : \RuEdu\Engine\Router::detectSiteUrl()) ?>">
                </div>
                <hr class="form-divider">
                <div class="form-group">
                    <label for="admin_login">Логин администратора</label>
                    <input type="text" id="admin_login" name="admin_login" required pattern="[a-zA-Z0-9._-]+">
                    <small>Латиница, цифры, точка, дефис, подчёркивание</small>
                </div>
                <div class="form-group">
                    <label for="admin_email">Email администратора</label>
                    <input type="email" id="admin_email" name="admin_email" required
                           value="<?= htmlspecialchars($installConfig['admin_email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="admin_password">Пароль</label>
                    <input type="password" id="admin_password" name="admin_password" required minlength="6">
                </div>
                <div class="actions">
                    <button type="submit" class="btn">
                        Завершить установку <span class="btn-arrow">→</span>
                    </button>
                </div>
            </form>

        <?php endif; ?>
        </main>
    </div>
</div>

<div id="install-loader" class="loader hidden" aria-live="polite" aria-busy="false">
    <div class="loader-panel">
        <div class="loader-spinner" aria-hidden="true"></div>
        <p class="loader-status" id="loader-status">Загрузка…</p>
        <div class="loader-bar">
            <div class="loader-bar-fill" id="loader-bar-fill"></div>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars($assetBase) ?>install.js"></script>
</body>
</html>
