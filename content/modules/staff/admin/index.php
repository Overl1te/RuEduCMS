<?php $title = 'Педагогический состав'; ?>
<div class="d-flex justify-content-between mb-4">
    <h2>Педагогический состав</h2>
    <a href="<?= url('admin/staff/create') ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Добавить</a>
</div>
<table class="table table-hover">
    <thead><tr><th>ФИО</th><th>Должность</th><th>Предмет</th><th>Статус</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($staff as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars($s['position']) ?></td>
            <td><?= htmlspecialchars($s['subject']) ?></td>
            <td><span class="badge bg-<?= $s['status'] === 'active' ? 'success' : 'secondary' ?>"><?= \RuEdu\Engine\Lang::userStatus($s['status']) ?></span></td>
            <td class="text-end">
                <a href="<?= url('admin/staff/edit/' . $s['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="<?= url('admin/staff/delete/' . $s['id']) ?>" class="d-inline" onsubmit="return confirm('Удалить?')">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
