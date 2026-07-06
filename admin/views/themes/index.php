<?php
use RuEdu\Engine\ThemeManager;
$title = 'Темы';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Темы оформления</h2>
</div>

<div class="card mb-4">
    <div class="card-header">Установить тему</div>
    <div class="card-body">
        <?php if (!$hasZip): ?>
            <div class="alert alert-danger mb-0">Расширение PHP ZIP не установлено. Установка тем из архива недоступна.</div>
        <?php else: ?>
            <p class="text-muted small mb-3">
                Загрузите ZIP-архив с папкой темы, внутри которой есть <code>theme.json</code> и <code>templates/layout.php</code>.
                Имя папки в архиве станет идентификатором темы (например, <code>my-school</code>).
            </p>
            <form method="POST" action="<?= url('admin/themes/install') ?>" enctype="multipart/form-data" class="row g-2 align-items-end">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                <div class="col-md-8">
                    <input type="file" name="theme_zip" class="form-control" accept=".zip" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload"></i> Установить тему
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <?php foreach ($themes as $theme): ?>
        <?php
        $slug = $theme['slug'] ?? '';
        $isActive = $activeSlug === $slug;
        $screenshotUrl = ThemeManager::screenshotUrl($theme);
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 <?= $isActive ? 'border-primary' : '' ?>">
                <?php if ($screenshotUrl): ?>
                    <img src="<?= htmlspecialchars($screenshotUrl) ?>" class="card-img-top" alt="" style="height: 160px; object-fit: cover;">
                <?php else: ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-muted" style="height: 160px;">
                        <i class="bi bi-image fs-1"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="mb-0"><?= htmlspecialchars($theme['name'] ?? $slug) ?></h5>
                        <?php if ($isActive): ?>
                            <span class="badge bg-primary">Активна</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted small flex-grow-1"><?= htmlspecialchars($theme['description'] ?? '') ?></p>
                    <div class="text-muted small mb-3">
                        <code><?= htmlspecialchars($slug) ?></code>
                        <?php if (!empty($theme['version'])): ?>
                            · v<?= htmlspecialchars($theme['version']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <?php if ($isActive): ?>
                            <button type="button" class="btn btn-success" disabled>
                                <i class="bi bi-check-lg"></i> Активна
                            </button>
                        <?php else: ?>
                            <form method="POST" action="<?= url('admin/themes/activate') ?>">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="bi bi-check-circle"></i> Активировать
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if (!empty($theme['customizer']['sections'])): ?>
                        <a href="<?= url('admin/themes/customize/' . rawurlencode($slug)) ?>" class="btn btn-primary">
                            <i class="bi bi-sliders"></i> Настроить оформление
                        </a>
                        <?php endif; ?>
                        <a href="<?= url('admin/themes/edit/' . rawurlencode($slug)) ?>" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Редактировать файлы
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if ($themes === []): ?>
    <div class="alert alert-info mt-3">Темы не найдены в папке <code>content/themes/</code>.</div>
<?php endif; ?>
