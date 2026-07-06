<?php $hideLayout = true; $title = 'Вход'; ?>
<div class="auth-page">
    <div class="auth-card card shadow-sm">
        <div class="card-body p-4">
            <h4 class="text-center mb-1"><i class="bi bi-mortarboard text-primary"></i> <?= htmlspecialchars(\RuEdu\Engine\Lang::appName()) ?></h4>
            <p class="text-center text-muted small mb-4">Вход в панель управления</p>
            <?php if ($flash_success ?? false): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
            <?php endif; ?>
            <?php if ($flash_error ?? false): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= url('admin/login') ?>">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                <div class="mb-3">
                    <label class="form-label">Логин</label>
                    <input type="text" name="login" class="form-control" required autofocus autocomplete="username">
                </div>
                <div class="mb-3">
                    <label class="form-label">Пароль</label>
                    <input type="password" name="password" class="form-control" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Войти</button>
                <div class="text-center auth-links">
                    <a href="<?= url('admin/forgot-password') ?>" class="text-muted small">Забыли пароль?</a>
                    <span class="text-muted mx-1">·</span>
                    <a href="<?= route('') ?>" class="text-muted small"><i class="bi bi-box-arrow-up-right"></i> На сайт</a>
                </div>
            </form>
        </div>
    </div>
</div>
