<section class="ed-cta">
    <div class="ed-container ed-cta__inner">
        <?php if (!empty($props['title'])): ?>
        <h2><?= htmlspecialchars((string) $props['title']) ?></h2>
        <?php endif; ?>
        <?php if (!empty($props['text'])): ?>
        <p><?= htmlspecialchars((string) $props['text']) ?></p>
        <?php endif; ?>
        <?php if (!empty($props['buttons']) && is_array($props['buttons'])): ?>
        <div class="ed-hero__actions">
            <?php foreach ($props['buttons'] as $btn): ?>
                <?php if (!is_array($btn) || empty($btn['label'])) continue; ?>
                <?php $style = ($btn['style'] ?? 'primary') === 'outline' ? 'ed-btn--outline' : 'ed-btn--primary'; ?>
                <a href="<?= htmlspecialchars(route(ltrim((string) ($btn['url'] ?? ''), '/'))) ?>" class="ed-btn <?= $style ?>">
                    <?= htmlspecialchars((string) $btn['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
