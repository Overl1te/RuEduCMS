<section class="ed-hero">
    <div class="ed-container ed-hero__inner">
        <?php if (!empty($props['badge'])): ?>
        <p class="ed-hero__label"><?= htmlspecialchars((string) $props['badge']) ?></p>
        <?php endif; ?>
        <h1 class="ed-hero__title"><?= htmlspecialchars((string) (($props['title'] ?? '') !== '' ? $props['title'] : $site_name)) ?></h1>
        <?php if (!empty($props['subtitle'])): ?>
        <p class="ed-hero__subtitle"><?= htmlspecialchars((string) $props['subtitle']) ?></p>
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
    <hr class="ed-rule ed-rule--wide">
</section>
