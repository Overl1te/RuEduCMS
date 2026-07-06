<?php $title = 'Справка — ' . ($section['title'] ?? 'Документация'); ?>
<div class="help-layout">
    <aside class="help-nav card">
        <div class="card-header fw-semibold">
            <i class="bi bi-book me-1"></i> Справка
        </div>
        <div class="card-body p-0">
            <?php foreach ($groups as $groupKey => $groupTitle): ?>
                <?php if (empty($groupedSections[$groupKey])) continue; ?>
                <div class="help-nav-group">
                    <div class="help-nav-group-title"><?= htmlspecialchars($groupTitle) ?></div>
                    <ul class="nav flex-column">
                        <?php foreach ($groupedSections[$groupKey] as $item): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($slug === $item['slug']) ? 'active' : '' ?>"
                                   href="<?= url('admin/help/' . $item['slug']) ?>">
                                    <i class="bi <?= htmlspecialchars($item['icon']) ?> me-1"></i>
                                    <?= htmlspecialchars($item['title']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </aside>
    <article class="help-content card">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bi <?= htmlspecialchars($section['icon'] ?? 'bi-question-circle') ?> text-primary"></i>
            <h2 class="h5 mb-0"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
        </div>
        <div class="card-body help-content-body">
            <?= $content ?>
        </div>
    </article>
</div>
