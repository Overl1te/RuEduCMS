<?php $align = ($props['align'] ?? 'left') === 'center' ? 'ed-section__header--center' : ''; ?>
<section class="ed-section">
    <div class="ed-container">
        <header class="ed-section__header <?= $align ?>">
            <?php if (!empty($props['eyebrow'])): ?>
            <span class="ed-eyebrow"><?= htmlspecialchars((string) $props['eyebrow']) ?></span>
            <?php endif; ?>
            <?php if (!empty($props['title'])): ?>
            <h2 class="ed-section__title"><?= htmlspecialchars((string) $props['title']) ?></h2>
            <?php endif; ?>
            <?php if (!empty($props['subtitle'])): ?>
            <p class="ed-section__subtitle"><?= htmlspecialchars((string) $props['subtitle']) ?></p>
            <?php endif; ?>
        </header>
    </div>
</section>
