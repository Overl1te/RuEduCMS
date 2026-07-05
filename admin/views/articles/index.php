<?php $title = 'Новости'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Новости</h2>
    <a href="<?= url('admin/articles/create') ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Создать</a>
</div>
<table class="table table-hover">
    <thead><tr><th>Заголовок</th><th>Категория</th><th>Статус</th><th>Дата</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($articles as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['title']) ?></td>
            <td><?= htmlspecialchars($a['category_name'] ?? '—') ?></td>
            <td><span class="badge bg-<?= $a['status'] === 'published' ? 'success' : 'secondary' ?>"><?= \RuEdu\Engine\Lang::publishStatus($a['status']) ?></span></td>
            <td><?= $a['published_at'] ? date('d.m.Y', strtotime($a['published_at'])) : '—' ?></td>
            <td class="text-end">
                <a href="<?= url('admin/articles/edit/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="<?= url('admin/articles/delete/' . $a['id']) ?>" class="d-inline" onsubmit="return confirm('Удалить?')">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
