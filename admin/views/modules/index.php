<?php $title = 'Модули'; ?>
<h2 class="mb-4">Встроенные модули</h2>
<div class="row g-3">
    <?php foreach ($modules as $m): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5><?= htmlspecialchars($m['title']) ?></h5>
                        <form method="POST" action="<?= url('admin/modules/toggle/' . $m['id']) ?>">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" <?= $m['enabled'] ? 'checked' : '' ?> onchange="this.form.submit()">
                            </div>
                        </form>
                    </div>
                    <p class="text-muted small"><?= htmlspecialchars($m['description'] ?? '') ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
