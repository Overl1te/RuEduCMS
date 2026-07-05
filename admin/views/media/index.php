<?php $title = 'Медиабиблиотека'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Медиабиблиотека</h2>
    <form method="POST" action="<?= url('admin/media/upload') ?>" enctype="multipart/form-data" class="d-flex gap-2">
        <input type="file" name="file" class="form-control form-control-sm" required>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-upload"></i> Загрузить</button>
    </form>
</div>
<div class="row g-3">
    <?php foreach ($media as $m): ?>
        <div class="col-md-2">
            <div class="card">
                <?php if (str_starts_with($m['mime_type'], 'image/')): ?>
                    <img src="<?= url('uploads/' . $m['path']) ?>" class="card-img-top" style="height:120px;object-fit:cover;">
                <?php else: ?>
                    <div class="card-body text-center py-4"><i class="bi bi-file-earmark fs-1"></i></div>
                <?php endif; ?>
                <div class="card-body p-2">
                    <small class="text-truncate d-block"><?= htmlspecialchars($m['filename']) ?></small>
                    <form method="POST" action="<?= url('admin/media/delete/' . $m['id']) ?>" class="mt-1" onsubmit="return confirm('Удалить?')">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                        <button class="btn btn-sm btn-outline-danger w-100">Удалить</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
