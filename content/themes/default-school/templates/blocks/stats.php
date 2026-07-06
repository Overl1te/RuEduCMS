<?php if (!empty($props['items']) && is_array($props['items'])): ?>
<div class="stats-strip">
    <div class="stats-grid" data-animate>
        <?php foreach ($props['items'] as $i => $item): ?>
            <?php if (!is_array($item)) continue; ?>
            <div class="stat-item" data-animate data-animate-delay="<?= min($i + 1, 6) ?>">
                <div class="stat-value"<?= !empty($item['count']) ? ' data-count="' . htmlspecialchars((string) $item['count']) . '"' : '' ?>>
                    <?= htmlspecialchars((string) ($item['value'] ?? '')) ?>
                </div>
                <div class="stat-label"><?= htmlspecialchars((string) ($item['label'] ?? '')) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
