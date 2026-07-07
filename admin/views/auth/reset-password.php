<?php $hideLayout = true; $title = 'Новый пароль'; ?>
<div class="auth-page">
    <div class="auth-card card shadow-sm">
        <div class="card-body p-4">
            <h4 class="text-center mb-1"><i class="bi bi-shield-lock text-primary"></i> Новый пароль</h4>
            <p class="text-center text-muted small mb-4">Придумайте новый пароль для входа</p>
            <?php if ($flash_error ?? false): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= url('admin/reset-password') ?>">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                <div class="mb-3">
                    <label class="form-label">Новый пароль</label>
                    <input type="password" name="password" class="form-control" required minlength="8" autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Повторите пароль</label>
                    <input type="password" name="password_confirm" class="form-control" required minlength="8">
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Сохранить пароль</button>
                <div class="text-center auth-links">
                    <a href="<?= url('admin/login') ?>" class="text-muted small">← Вернуться ко входу</a>
                    <span class="text-muted mx-1">·</span>
                    <a href="<?= route('') ?>" class="text-muted small"><i class="bi bi-box-arrow-up-right"></i> На сайт</a>
                </div>
            </form>
        </div>
    </div>
</div>
