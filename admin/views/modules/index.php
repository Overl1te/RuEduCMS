<?php $title = 'Модули'; ?>

<?php
$renderModuleCard = static function (array $m): void {
    ?>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <h5 class="mb-0"><?= htmlspecialchars($m['title']) ?></h5>
                    <form method="POST" action="<?= url('admin/modules/toggle/' . $m['id']) ?>">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" <?= $m['enabled'] ? 'checked' : '' ?> onchange="this.form.submit()">
                        </div>
                    </form>
                </div>
                <p class="text-muted small mt-2 mb-0"><?= htmlspecialchars($m['description'] ?? '') ?></p>
            </div>
        </div>
    </div>
    <?php
};
?>

<h2 class="mb-4">Модули и разделы</h2>

<h5 class="text-muted mb-3">Функциональные модули</h5>
<div class="row g-3 mb-4">
    <?php if (empty($codeModules)): ?>
        <div class="col-12"><div class="alert alert-light border mb-0">Нет установленных модулей.</div></div>
    <?php else: ?>
        <?php foreach ($codeModules as $m): ?>
            <?php $renderModuleCard($m); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<h5 class="text-muted mb-3">Разделы сайта</h5>
<p class="text-muted small">Типовые страницы из структуры школы. Отключение скрывает раздел в меню, на карте сайта и по прямой ссылке.</p>
<div class="row g-3">
    <?php if (empty($sectionModules)): ?>
        <div class="col-12"><div class="alert alert-light border mb-0">Разделы ещё не созданы. Обновите базу данных в разделе «Обновления».</div></div>
    <?php else: ?>
        <?php foreach ($sectionModules as $m): ?>
            <?php $renderModuleCard($m); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
