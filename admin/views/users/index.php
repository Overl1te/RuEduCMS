<?php $title = 'Пользователи'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Пользователи</h2>
    <a href="<?= url('admin/users/create') ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить</a>
</div>
<table class="table table-hover">
    <thead><tr><th>Логин</th><th>Email</th><th>Роль</th><th>Статус</th><th></th></tr></thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><code><?= htmlspecialchars($u['login'] ?? $u['name']) ?></code></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge bg-info"><?= \RuEdu\Engine\Lang::role($u['role']) ?></span></td>
            <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'secondary' ?>"><?= \RuEdu\Engine\Lang::userStatus($u['status']) ?></span></td>
            <td class="text-end">
                <a href="<?= url('admin/users/edit/' . $u['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
