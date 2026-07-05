<?php $title = 'Входящие заявки'; ?>
<h2 class="mb-4">Входящие заявки</h2>
<table class="table table-hover">
    <thead><tr><th>Форма</th><th>Данные</th><th>Дата</th><th>Статус</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($submissions as $s): ?>
        <tr class="<?= !$s['is_read'] ? 'table-warning' : '' ?>">
            <td><?= htmlspecialchars($s['form_name'] ?? '—') ?></td>
            <td>
                <?php $data = json_decode($s['data'], true); ?>
                <?php foreach ($data as $k => $v): ?>
                    <small><strong><?= htmlspecialchars(field_label((string) $k)) ?>:</strong> <?= htmlspecialchars((string) $v) ?></small><br>
                <?php endforeach; ?>
            </td>
            <td><?= date('d.m.Y H:i', strtotime($s['created_at'])) ?></td>
            <td><?= $s['is_read'] ? 'Прочитано' : 'Новое' ?></td>
            <td>
                <?php if (!$s['is_read']): ?>
                    <form method="POST" action="<?= url('admin/forms/submissions/read/' . $s['id']) ?>">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                        <button class="btn btn-sm btn-outline-primary">Прочитано</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
