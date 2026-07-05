<?php
use RuEdu\Engine\Config;
$title = 'Темы';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Темы оформления</h2>
</div>
<div class="row g-3">
    <?php foreach ($themes as $theme): ?>
        <?php
        $slug = $theme['slug'] ?? '';
        $isActive = Config::get('theme') === $slug;
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 <?= $isActive ? 'border-primary' : '' ?>">
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
                    <a href="<?= url('admin/themes/edit/' . rawurlencode($slug)) ?>" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Редактировать файлы
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if ($themes === []): ?>
    <div class="alert alert-info">Темы не найдены в папке <code>content/themes/</code>.</div>
<?php endif; ?>
