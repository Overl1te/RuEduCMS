<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Админка') ?> — <?= htmlspecialchars(\RuEdu\Engine\Lang::appName()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= url('admin/assets/css/admin.css') ?>" rel="stylesheet">
</head>
<body>
<?php if (!isset($hideLayout)): ?>
<div class="d-flex admin-layout">
    <nav class="sidebar bg-dark text-white p-3">
        <h5 class="mb-4"><i class="bi bi-mortarboard"></i> <?= htmlspecialchars(\RuEdu\Engine\Lang::appName()) ?></h5>
        <ul class="nav flex-column">
            <?php foreach ($admin_menu ?? [] as $item): ?>
                <li class="nav-item">
                    <a class="nav-link text-white-50 <?= !empty($item['active']) ? 'active text-white' : '' ?>"
                       href="<?= htmlspecialchars($item['url']) ?>">
                        <i class="bi <?= $item['icon'] ?? 'bi-circle' ?>"></i>
                        <?= htmlspecialchars($item['title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="mt-auto pt-4 border-top border-secondary">
            <a href="<?= url('') ?>" class="nav-link text-white-50" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Сайт</a>
            <a href="<?= url('admin/logout') ?>" class="nav-link text-white-50"><i class="bi bi-box-arrow-right"></i> Выход</a>
        </div>
    </nav>
    <main class="admin-main p-4">
        <?php if ($flash_success ?? false): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
        <?php endif; ?>
        <?php if ($flash_error ?? false): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </main>
</div>
<?php else: ?>
    <?= $content ?>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('admin/assets/js/admin.js') ?>"></script>
</body>
</html>
