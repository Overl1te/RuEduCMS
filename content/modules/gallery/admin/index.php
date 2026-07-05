<?php $title = 'Галерея'; ?>
<div class="d-flex justify-content-between mb-4">
    <h2>Фотогалерея</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#albumModal"><i class="bi bi-plus"></i> Альбом</button>
</div>
<div class="row g-3">
    <?php foreach ($albums as $a): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5><?= htmlspecialchars($a['title']) ?></h5>
                    <p class="text-muted"><?= $a['image_count'] ?> фото</p>
                    <form method="POST" action="<?= url('admin/gallery/upload/' . $a['id']) ?>" enctype="multipart/form-data">
                        <input type="file" name="images[]" multiple accept="image/*" class="form-control form-control-sm mb-2">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Загрузить фото</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="modal fade" id="albumModal" tabindex="-1">
    <div class="modal-dialog"><form method="POST" action="<?= url('admin/gallery/album/save') ?>" class="modal-content">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="modal-header"><h5 class="modal-title">Новый альбом</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label">Название</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Описание</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Создать</button></div>
    </form></div>
</div>
