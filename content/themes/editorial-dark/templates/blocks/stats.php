<?php if (!empty($props['items']) && is_array($props['items'])): ?>
<section class="ed-section ed-stats">
    <div class="ed-container">
        <div class="ed-stats__grid">
            <?php foreach ($props['items'] as $item): ?>
                <?php if (!is_array($item)) continue; ?>
                <div class="ed-stat">
                    <span class="ed-stat__value"><?= htmlspecialchars((string) ($item['value'] ?? '')) ?></span>
                    <span class="ed-stat__label"><?= htmlspecialchars((string) ($item['label'] ?? '')) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
