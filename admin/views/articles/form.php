<?php $title = $article ? 'Редактирование новости' : 'Новая новость'; ?>
<h2 class="mb-4"><?= $title ?></h2>
<form method="POST" action="<?= url('admin/articles/save') ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
    <?php if ($article): ?><input type="hidden" name="id" value="<?= $article['id'] ?>"><?php endif; ?>
    <div class="row">
        <div class="col-md-8">
            <div class="mb-3">
                <label class="form-label">Заголовок</label>
                <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($article['title'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Краткое описание</label>
                <textarea name="excerpt" class="form-control" rows="2"><?= htmlspecialchars($article['excerpt'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Содержимое</label>
                <textarea name="content" id="editor" class="form-control" rows="15"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3"><div class="card-body">
                <label class="form-label">Категория</label>
                <select name="category_id" class="form-select mb-2">
                    <option value="">—</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($article['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="form-label">Статус</label>
                <select name="status" class="form-select">
                    <option value="draft" <?= ($article['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Черновик</option>
                    <option value="published" <?= ($article['status'] ?? '') === 'published' ? 'selected' : '' ?>>Опубликована</option>
                </select>
                <button type="submit" class="btn btn-primary w-100 mt-3">Сохранить</button>
            </div></div>
            <div class="card"><div class="card-body">
                <label class="form-label">Заголовок для поисковиков</label>
                <input type="text" name="meta_title" class="form-control mb-2" value="<?= htmlspecialchars($article['meta_title'] ?? '') ?>">
                <label class="form-label">Описание для поисковиков</label>
                <textarea name="meta_description" class="form-control" rows="3"><?= htmlspecialchars($article['meta_description'] ?? '') ?></textarea>
            </div></div>
        </div>
    </div>
</form>
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>tinymce.init({selector:'#editor',height:400,language:'ru',plugins:'link image lists table code',toolbar:'undo redo | bold italic | link image | bullist numlist | table | code'});</script>
