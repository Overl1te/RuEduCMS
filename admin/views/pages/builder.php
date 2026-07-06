<?php
/** @var string $builderType */
/** @var string $title */
/** @var string $saveUrl */
/** @var string $backUrl */
/** @var list<array{type: string, props: array<string, mixed>}> $blocks */
/** @var array<string, array<string, mixed>> $blockTypes */
/** @var array<string, mixed>|null $page */

$blocksJson = json_encode($blocks, JSON_UNESCAPED_UNICODE);
$typesJson = json_encode($blockTypes, JSON_UNESCAPED_UNICODE);
?>
<link href="<?= url('admin/assets/css/page-builder.css') ?>" rel="stylesheet">

<div class="page-builder-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?= htmlspecialchars($backUrl) ?>" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Назад</a>
        <h2 class="mb-0 mt-1"><?= htmlspecialchars($title) ?></h2>
        <p class="text-muted small mb-0">Перетаскивайте блоки для изменения порядка</p>
    </div>
    <button type="submit" form="builderForm" class="btn btn-primary">
        <i class="bi bi-check-lg"></i> Сохранить
    </button>
</div>

<div class="page-builder">
    <aside class="page-builder__palette card">
        <div class="card-header py-2"><strong class="small">Блоки</strong></div>
        <div class="card-body p-2" id="blockPalette">
            <?php foreach ($blockTypes as $type => $meta): ?>
                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2 palette-block"
                        data-type="<?= htmlspecialchars($type) ?>">
                    <i class="bi <?= htmlspecialchars($meta['icon'] ?? 'bi-square') ?>"></i>
                    <?= htmlspecialchars($meta['label'] ?? $type) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </aside>

    <div class="page-builder__canvas card">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <strong class="small">Структура страницы</strong>
            <span class="text-muted small" id="blockCount"><?= count($blocks) ?> блок(ов)</span>
        </div>
        <div class="card-body">
            <div id="blockCanvas" class="block-canvas">
                <?php if ($blocks === []): ?>
                    <div class="block-canvas__empty text-muted text-center py-5" id="canvasEmpty">
                        Добавьте блок из палитры слева
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <aside class="page-builder__props card">
        <div class="card-header py-2"><strong class="small">Свойства блока</strong></div>
        <div class="card-body" id="blockPropsPanel">
            <p class="text-muted small mb-0">Выберите блок на холсте</p>
        </div>
    </aside>
</div>

<form method="POST" action="<?= htmlspecialchars($saveUrl) ?>" id="builderForm">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
    <?php if ($builderType === 'page' && $page): ?>
        <input type="hidden" name="id" value="<?= (int) $page['id'] ?>">
    <?php endif; ?>
    <input type="hidden" name="blocks" id="blocksInput" value="<?= htmlspecialchars($blocksJson) ?>">
</form>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="<?= url('admin/assets/js/page-builder.js') ?>"></script>
<script>
window.pageBuilderConfig = {
    blocks: <?= $blocksJson ?: '[]' ?>,
    blockTypes: <?= $typesJson ?: '{}' ?>
};
</script>
