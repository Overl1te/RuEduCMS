<?php
use RuEdu\Engine\Version;
$title = 'Обновления';
$isUpToDate = Version::isUpToDate() && !$remoteUpdate;
?>
<h2 class="mb-4">Обновления CMS</h2>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-muted">Версия файлов</h6>
                <h4 class="mb-0"><?= htmlspecialchars($currentVersion) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-muted">Версия базы данных</h6>
                <h4 class="mb-0"><?= htmlspecialchars($dbVersion) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-muted">Статус</h6>
                <?php if ($isUpToDate && empty($pendingMigrations)): ?>
                    <span class="badge bg-success fs-6">Актуальная версия</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark fs-6">Требуется обновление</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($pendingMigrations)): ?>
<div class="alert alert-warning">
    <i class="bi bi-database-gear"></i>
    Ожидают выполнения миграции БД: <?= htmlspecialchars(implode(', ', $pendingMigrations)) ?>
    <form method="POST" action="<?= url('admin/updates/migrate') ?>" class="d-inline ms-2">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
        <button type="submit" class="btn btn-sm btn-warning">Выполнить миграции</button>
    </form>
</div>
<?php elseif (version_compare($currentVersion, $dbVersion, '>')): ?>
<div class="alert alert-info">
    <i class="bi bi-database-check"></i>
    Файлы CMS обновлены, но версия БД отстаёт.
    <form method="POST" action="<?= url('admin/updates/migrate') ?>" class="d-inline ms-2">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
        <button type="submit" class="btn btn-sm btn-primary">Синхронизировать БД</button>
    </form>
</div>
<?php endif; ?>

<?php if ($remoteUpdate): ?>
<div class="alert alert-info">
    <i class="bi bi-cloud-download"></i>
    Доступно обновление до версии <?= htmlspecialchars($remoteUpdate['version']) ?>
    <?php if (!empty($remoteUpdate['url'])): ?>
        <a href="<?= htmlspecialchars($remoteUpdate['url']) ?>" class="alert-link" target="_blank">Подробнее</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Установка обновления из архива</div>
            <div class="card-body">
                <?php if (!$hasZip): ?>
                    <div class="alert alert-danger mb-0">Расширение PHP ZIP не установлено. Автоустановка недоступна.</div>
                <?php else: ?>
                    <p class="text-muted small">
                        Загрузите ZIP-архив с папками <code>core/</code> и <code>admin/</code>.
                        Папки <code>content/</code> и <code>config.php</code> не затрагиваются.
                    </p>
                    <form method="POST" action="<?= url('admin/updates/upload') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                        <div class="mb-3">
                            <input type="file" name="package" class="form-control" accept=".zip" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Установить обновление
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Автоматические обновления</div>
            <div class="card-body">
                <?php if ($updateSource): ?>
                    <p>Источник: <strong><?= htmlspecialchars((string) $updateSource) ?></strong></p>
                <?php else: ?>
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle"></i>
                        Источник обновлений не настроен. Укажите <code>update_source</code> в config.php
                        (например, <code>'github'</code>), когда репозиторий будет готов.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Резервные копии</span>
                <form method="POST" action="<?= url('admin/updates/backup') ?>" class="mb-0">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-archive"></i> Создать копию
                    </button>
                </form>
            </div>
            <div class="card-body">
                <?php if (empty($backups)): ?>
                    <p class="text-muted mb-0 small">Резервных копий пока нет. Копия создаётся автоматически перед обновлением.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach (array_slice($backups, 0, 5) as $backup): ?>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><?= htmlspecialchars($backup['file']) ?></span>
                                <small class="text-muted"><?= htmlspecialchars($backup['date']) ?> · <?= number_format($backup['size'] / 1024, 0) ?> КБ</small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
