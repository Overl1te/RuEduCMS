<?php $align = ($props['align'] ?? 'left') === 'center' ? 'text-center' : ''; ?>
<section class="section">
    <div class="container">
        <div class="section-header <?= $align ?>" data-animate>
            <?php if (!empty($props['eyebrow'])): ?>
            <span class="section-eyebrow"><?= htmlspecialchars((string) $props['eyebrow']) ?></span>
            <?php endif; ?>
            <?php if (!empty($props['title'])): ?>
            <h2 class="section-title"><?= htmlspecialchars((string) $props['title']) ?></h2>
            <?php endif; ?>
            <?php if (!empty($props['subtitle'])): ?>
            <p class="section-subtitle"><?= htmlspecialchars((string) $props['subtitle']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
