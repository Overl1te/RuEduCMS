<?php $title = 'Документы'; ?>
<div class="d-flex justify-content-between mb-4">
    <h2>Документы</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#docModal"><i class="bi bi-plus"></i> Добавить</button>
</div>
<table class="table table-hover">
    <thead><tr><th>Название</th><th>Категория</th><th>Дата</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($docs as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['title']) ?></td>
            <td><?= htmlspecialchars($d['category']) ?></td>
            <td><?= $d['published_at'] ? date('d.m.Y', strtotime($d['published_at'])) : '—' ?></td>
            <td class="text-end">
                <form method="POST" action="<?= url('admin/documents/delete/' . $d['id']) ?>" class="d-inline" onsubmit="return confirm('Удалить?')">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="modal fade" id="docModal" tabindex="-1">
    <div class="modal-dialog"><form method="POST" action="<?= url('admin/documents/save') ?>" enctype="multipart/form-data" class="modal-content">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="modal-header"><h5 class="modal-title">Добавить документ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label">Название</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Категория</label><input type="text" name="category" class="form-control" value="Общие"></div>
            <div class="mb-2"><label class="form-label">Файл</label><input type="file" name="file" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Дата публикации</label><input type="date" name="published_at" class="form-control"></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Сохранить</button></div>
    </form></div>
</div>
