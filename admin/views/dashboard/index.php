<?php $title = 'Панель управления'; ?>
<h2 class="mb-4">Панель управления</h2>
<?php if (!empty($recommendations)): ?>
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning-subtle d-flex align-items-center gap-2">
        <i class="bi bi-lightbulb text-warning"></i>
        <span class="fw-semibold">Рекомендации по настройке сайта</span>
        <span class="badge bg-warning text-dark ms-auto"><?= count($recommendations) ?></span>
    </div>
    <div class="list-group list-group-flush">
        <?php foreach ($recommendations as $rec): ?>
            <div class="list-group-item d-flex align-items-start gap-3 py-3">
                <div class="fs-4 text-primary lh-1 pt-1"><i class="bi <?= htmlspecialchars($rec['icon']) ?>"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold"><?= htmlspecialchars($rec['title']) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($rec['description']) ?></div>
                    <?php if (!empty($rec['progress'])): ?>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?= (int) $rec['progress'] ?>%;"></div>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="<?= htmlspecialchars($rec['url']) ?>" class="btn btn-sm btn-outline-primary flex-shrink-0">Перейти</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card"><div class="card-body text-center">
            <h3 class="text-primary"><?= $stats['pages'] ?></h3>
            <small class="text-muted">Страниц</small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body text-center">
            <h3 class="text-success"><?= $stats['articles'] ?></h3>
            <small class="text-muted">Новостей</small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body text-center">
            <h3 class="text-info"><?= $stats['media'] ?></h3>
            <small class="text-muted">Медиафайлов</small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body text-center">
            <h3 class="text-warning"><?= $stats['forms_unread'] ?></h3>
            <small class="text-muted">Новых заявок</small>
        </div></div>
    </div>
</div>
<?php if ($update): ?>
<div class="alert alert-info">
    <i class="bi bi-arrow-up-circle"></i> Доступно обновление до версии <?= htmlspecialchars($update['version']) ?>
    <?php if (!empty($update['url'])): ?>
        <a href="<?= htmlspecialchars($update['url']) ?>" class="alert-link" target="_blank">Подробнее</a>
    <?php endif; ?>
    <a href="<?= url('admin/updates') ?>" class="alert-link ms-2">Перейти к обновлениям</a>
</div>
<?php elseif ($needsDbUpdate ?? false): ?>
<div class="alert alert-warning">
    <i class="bi bi-database-gear"></i> Требуется обновление базы данных
    <a href="<?= url('admin/updates') ?>" class="alert-link ms-2">Обновить</a>
</div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <h5>Быстрые действия</h5>
        <div class="d-flex gap-2 mt-3">
            <a href="<?= url('admin/pages/create') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-plus"></i> Страница</a>
            <a href="<?= url('admin/articles/create') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-plus"></i> Новость</a>
            <a href="<?= url('admin/media') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-upload"></i> Медиа</a>
            <a href="<?= url('admin/settings') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-gear"></i> Настройки</a>
        </div>
    </div>
</div>
