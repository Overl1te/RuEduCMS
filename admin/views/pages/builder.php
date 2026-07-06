<?php
/** @var string $builderType */
/** @var string $entityKey */
/** @var string $title */
/** @var string $saveUrl */
/** @var string $backUrl */
/** @var string $previewUrl */
/** @var list<array<string, mixed>> $values */
/** @var array<string, array<string, mixed>> $layouts */
/** @var array<string, mixed>|null $page */
/** @var string|null $systemId */

$valuesJson = json_encode($values, JSON_UNESCAPED_UNICODE);
$layoutsJson = json_encode($layouts, JSON_UNESCAPED_UNICODE);
$styleKeys = \RuEdu\Engine\ElementStyles::styleKeys();
$pages = \RuEdu\Model\Page::getAll('published');
?>
<link href="<?= url('admin/assets/css/page-builder.css') ?>" rel="stylesheet">

<div class="page-builder-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?= htmlspecialchars($backUrl) ?>" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Назад</a>
        <h2 class="mb-0 mt-1"><?= htmlspecialchars($title) ?></h2>
        <p class="text-muted small mb-0">Гибкий контент с полями, стилями и предпросмотром</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" id="refreshPreview"><i class="bi bi-arrow-clockwise"></i> Обновить preview</button>
        <button type="submit" form="builderForm" class="btn btn-primary"><i class="bi bi-check-lg"></i> Сохранить</button>
    </div>
</div>

<div class="page-builder page-builder--v2">
    <aside class="page-builder__palette card">
        <div class="card-header py-2"><strong class="small">Layout'ы</strong></div>
        <div class="card-body p-2" id="layoutPalette">
            <?php foreach ($layouts as $name => $meta): ?>
                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2 palette-layout"
                        data-layout="<?= htmlspecialchars($name) ?>">
                    <i class="bi <?= htmlspecialchars($meta['icon'] ?? 'bi-square') ?>"></i>
                    <?= htmlspecialchars($meta['label'] ?? $name) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </aside>

    <div class="page-builder__canvas card">
        <div class="card-header py-2 d-flex justify-content-between">
            <strong class="small">Структура</strong>
            <span class="text-muted small" id="rowCount">0</span>
        </div>
        <div class="card-body p-0 d-flex flex-column" style="min-height:0">
            <div class="p-3 border-bottom flex-grow-0" style="max-height:40%;overflow:auto">
                <div id="rowCanvas" class="block-canvas"></div>
            </div>
            <div class="flex-grow-1 p-0" style="min-height:280px">
                <iframe id="builderPreview" src="<?= htmlspecialchars($previewUrl) ?>" title="Предпросмотр" style="width:100%;height:100%;min-height:280px;border:0"></iframe>
            </div>
        </div>
    </div>

    <aside class="page-builder__props card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabContent" type="button">Контент</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabBlockStyle" type="button">Блок</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabElementStyle" type="button">Элементы</button></li>
            </ul>
        </div>
        <div class="card-body tab-content">
            <div class="tab-pane fade show active" id="tabContent">
                <div id="fieldEditorPanel"><p class="text-muted small">Выберите блок</p></div>
            </div>
            <div class="tab-pane fade" id="tabBlockStyle">
                <div id="blockStylePanel"><p class="text-muted small">Выберите блок</p></div>
            </div>
            <div class="tab-pane fade" id="tabElementStyle">
                <div id="elementStylePanel"><p class="text-muted small">Выберите блок</p></div>
            </div>
        </div>
    </aside>
</div>

<form method="POST" action="<?= htmlspecialchars($saveUrl) ?>" id="builderForm">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
    <?php if ($builderType === 'page' && $page): ?>
        <input type="hidden" name="id" value="<?= (int) $page['id'] ?>">
    <?php endif; ?>
    <?php if ($builderType === 'system' && !empty($systemId)): ?>
        <input type="hidden" name="system_id" value="<?= htmlspecialchars($systemId) ?>">
    <?php endif; ?>
    <input type="hidden" name="field_data" id="fieldDataInput" value="<?= htmlspecialchars($valuesJson) ?>">
</form>

<div class="modal fade" id="mediaPickerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Медиа</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="row g-2" id="mediaPickerGrid"></div></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script src="<?= url('admin/assets/js/field-editor.js') ?>"></script>
<script src="<?= url('admin/assets/js/page-builder.js') ?>"></script>
<script>
window.pageBuilderConfig = {
    entity: <?= json_encode($entityKey, JSON_UNESCAPED_UNICODE) ?>,
    rows: <?= $valuesJson ?: '[]' ?>,
    layouts: <?= $layoutsJson ?: '{}' ?>,
    styleKeys: <?= json_encode($styleKeys, JSON_UNESCAPED_UNICODE) ?>,
    pages: <?= json_encode(array_map(static fn ($p) => ['title' => $p['title'], 'slug' => $p['slug']], $pages), JSON_UNESCAPED_UNICODE) ?>,
    previewUrl: <?= json_encode($previewUrl, JSON_UNESCAPED_UNICODE) ?>,
    apiPreview: <?= json_encode(url('admin/api/preview/render'), JSON_UNESCAPED_UNICODE) ?>,
    apiMedia: <?= json_encode(url('admin/api/media'), JSON_UNESCAPED_UNICODE) ?>
};
</script>
