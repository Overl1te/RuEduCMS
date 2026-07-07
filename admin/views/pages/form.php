<?php $title = $page ? 'Редактирование страницы' : 'Новая страница'; ?>
<h2 class="mb-4"><?= $title ?></h2>
<form method="POST" action="<?= url('admin/pages/save') ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
    <?php if ($page): ?><input type="hidden" name="id" value="<?= $page['id'] ?>"><?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="mb-3">
                <label class="form-label">Заголовок</label>
                <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($page['title'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">URL (slug)</label>
                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($page['slug'] ?? '') ?>" placeholder="Авто из заголовка">
            </div>
            <div class="mb-3">
                <label class="form-label">Содержимое</label>
                <textarea name="content" id="editor" class="form-control" rows="15"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3"><div class="card-body">
                <label class="form-label">Статус</label>
                <select name="status" class="form-select">
                    <option value="draft" <?= ($page['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Черновик</option>
                    <option value="published" <?= ($page['status'] ?? '') === 'published' ? 'selected' : '' ?>>Опубликована</option>
                </select>
                <button type="submit" class="btn btn-primary w-100 mt-3">Сохранить</button>
            </div></div>
            <div class="card"><div class="card-body">
                <label class="form-label">Заголовок для поисковиков</label>
                <input type="text" name="meta_title" class="form-control mb-2" value="<?= htmlspecialchars($page['meta_title'] ?? '') ?>">
                <label class="form-label">Описание для поисковиков</label>
                <textarea name="meta_description" class="form-control" rows="3"><?= htmlspecialchars($page['meta_description'] ?? '') ?></textarea>
            </div></div>
        </div>
    </div>
</form>
<?php include ADMIN_PATH . '/views/partials/tinymce.php'; ?>
