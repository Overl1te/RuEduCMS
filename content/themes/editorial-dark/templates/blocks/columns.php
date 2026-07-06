<?php if (!empty($props['columns']) && is_array($props['columns'])): ?>
<section class="ed-section">
    <div class="ed-container">
        <div class="ed-columns ed-columns--<?= count($props['columns']) ?>">
            <?php foreach ($props['columns'] as $col): ?>
                <?php if (!is_array($col)) continue; ?>
                <div class="ed-columns__col">
                    <?php if (!empty($col['title'])): ?>
                    <h3><?= htmlspecialchars((string) $col['title']) ?></h3>
                    <?php endif; ?>
                    <div class="ed-content-body"><?= $col['content'] ?? '' ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
