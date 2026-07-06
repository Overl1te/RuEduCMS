<?php
use RuEdu\Engine\FieldTypeRegistry;
use RuEdu\Engine\FieldLocation;

$title = $group ? 'Редактирование группы' : 'Новая группа';
$fieldTypes = FieldTypeRegistry::getAll();
$locationParams = FieldLocation::locationParams();
$fieldsJson = json_encode($fieldsTree ?? [], JSON_UNESCAPED_UNICODE);
$locationsJson = json_encode(\RuEdu\Engine\FieldLocation::parseRules($group['locations'] ?? null), JSON_UNESCAPED_UNICODE);
?>
<link href="<?= url('admin/assets/css/field-group-builder.css') ?>" rel="stylesheet">

<h2 class="mb-4"><?= htmlspecialchars($title) ?></h2>
<form method="POST" action="<?= url('admin/field-groups/save') ?>" id="fgForm">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="id" value="<?= (int) ($group['id'] ?? 0) ?>">
    <input type="hidden" name="fields_json" id="fieldsJson" value="<?= htmlspecialchars($fieldsJson) ?>">

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">Группа</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Название</label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($group['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($group['slug'] ?? '') ?>" placeholder="home-page">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Порядок</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int) ($group['sort_order'] ?? 0) ?>">
                    </div>
                    <div class="form-check mb-0">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" <?= !isset($group['is_active']) || !empty($group['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isActive">Активна</label>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Правила показа</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addLocationRule">+</button>
                </div>
                <div class="card-body" id="locationRules"></div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Поля</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" id="addFlexibleField">+ Flexible</button>
                        <button type="button" class="btn btn-outline-secondary" id="addFieldBtn">+ Поле</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="fieldsTree" class="fg-fields-tree"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="<?= url('admin/field-groups') ?>" class="btn btn-outline-secondary">Отмена</a>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="<?= url('admin/assets/js/field-group-builder.js') ?>"></script>
<script>
window.fgBuilderConfig = {
    fields: <?= $fieldsJson ?: '[]' ?>,
    locations: <?= $locationsJson ?: '[]' ?>,
    fieldTypes: <?= json_encode($fieldTypes, JSON_UNESCAPED_UNICODE) ?>,
    locationParams: <?= json_encode($locationParams, JSON_UNESCAPED_UNICODE) ?>
};
</script>
