<?php $hideLayout = true; $title = 'Восстановление пароля'; ?>
<div class="auth-page">
    <div class="auth-card card shadow-sm">
        <div class="card-body p-4">
            <h4 class="text-center mb-1"><i class="bi bi-key text-primary"></i> Забыли пароль?</h4>
            <p class="text-center text-muted small mb-4">Введите логин или email — отправим ссылку для сброса</p>
            <?php if ($flash_success ?? false): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
            <?php endif; ?>
            <?php if ($flash_error ?? false): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= url('admin/forgot-password') ?>">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                <div class="mb-3">
                    <label class="form-label">Логин или email</label>
                    <input type="text" name="login" class="form-control" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Отправить ссылку</button>
                <div class="text-center auth-links">
                    <a href="<?= url('admin/login') ?>" class="text-muted small">← Вернуться ко входу</a>
                    <span class="text-muted mx-1">·</span>
                    <a href="<?= route('') ?>" class="text-muted small"><i class="bi bi-box-arrow-up-right"></i> На сайт</a>
                </div>
            </form>
        </div>
    </div>
</div>
