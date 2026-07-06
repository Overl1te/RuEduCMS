<section class="section quick-links section-alt">
    <div class="container">
        <div class="section-header" data-animate>
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
        <?php if (!empty($props['links']) && is_array($props['links'])): ?>
        <div class="links-grid">
            <?php foreach ($props['links'] as $i => $link): ?>
                <?php
                if (!is_array($link) || empty($link['label'])) {
                    continue;
                }
                $url = (string) ($link['url'] ?? '');
                if ($url !== '' && !\RuEdu\Engine\Modules::isUrlEnabled($url)) {
                    continue;
                }
                $icon = (string) ($link['icon'] ?? 'docs');
                ?>
                <a href="<?= htmlspecialchars(route(ltrim($url, '/'))) ?>" class="link-card" data-animate data-animate-delay="<?= min($i + 1, 6) ?>">
                    <span class="link-icon link-icon-<?= htmlspecialchars($icon) ?>" aria-hidden="true"></span>
                    <span class="link-label"><?= htmlspecialchars((string) $link['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
