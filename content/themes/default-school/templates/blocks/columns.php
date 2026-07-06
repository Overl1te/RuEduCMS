<?php if (!empty($props['columns']) && is_array($props['columns'])): ?>
<section class="section">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($props['columns'] as $col): ?>
                <?php if (!is_array($col)) continue; ?>
                <div class="col-md-<?= count($props['columns']) <= 2 ? 6 : 4 ?>">
                    <?php if (!empty($col['title'])): ?>
                    <h3><?= htmlspecialchars((string) $col['title']) ?></h3>
                    <?php endif; ?>
                    <div class="content-body"><?= $col['content'] ?? '' ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
