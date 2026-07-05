<?php $title = $editUser ? 'Редактирование пользователя' : 'Новый пользователь'; ?>
<h2 class="mb-4"><?= $title ?></h2>
<form method="POST" action="<?= url('admin/users/save') ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
    <?php if ($editUser): ?><input type="hidden" name="id" value="<?= $editUser['id'] ?>"><?php endif; ?>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Имя</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editUser['name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Логин</label>
                <input type="text" name="login" class="form-control" required pattern="[a-zA-Z0-9._-]+"
                       value="<?= htmlspecialchars($editUser['login'] ?? '') ?>" autocomplete="username">
                <div class="form-text">Латиница, цифры, точка, дефис, подчёркивание</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($editUser['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Пароль <?= $editUser ? '(оставьте пустым, чтобы не менять)' : '' ?></label>
                <input type="password" name="password" class="form-control" <?= $editUser ? '' : 'required' ?>>
            </div>
            <div class="mb-3">
                <label class="form-label">Роль</label>
                <select name="role" class="form-select">
                    <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Администратор</option>
                    <option value="editor" <?= ($editUser['role'] ?? '') === 'editor' ? 'selected' : '' ?>>Редактор</option>
                    <option value="author" <?= ($editUser['role'] ?? 'author') === 'author' ? 'selected' : '' ?>>Автор</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Статус</label>
                <select name="status" class="form-select">
                    <option value="active" <?= ($editUser['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Активен</option>
                    <option value="inactive" <?= ($editUser['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Неактивен</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </div>
</form>
