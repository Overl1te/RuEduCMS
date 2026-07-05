<?php $title = 'Страницы'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Страницы</h2>
    <a href="<?= url('admin/pages/create') ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Создать</a>
</div>
<table class="table table-hover">
    <thead><tr><th>Название</th><th>URL</th><th>Статус</th><th>Дата</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($pages as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['title']) ?></td>
            <td><code>/page/<?= htmlspecialchars($p['slug']) ?></code></td>
            <td><span class="badge bg-<?= $p['status'] === 'published' ? 'success' : 'secondary' ?>"><?= \RuEdu\Engine\Lang::publishStatus($p['status']) ?></span></td>
            <td><?= date('d.m.Y', strtotime($p['updated_at'])) ?></td>
            <td class="text-end">
                <a href="<?= url('admin/pages/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="<?= url('admin/pages/delete/' . $p['id']) ?>" class="d-inline" onsubmit="return confirm('Удалить?')">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
