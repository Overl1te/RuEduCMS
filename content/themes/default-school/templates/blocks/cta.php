<section class="cta-band">
    <div class="container cta-band__inner" data-animate>
        <?php if (!empty($props['title'])): ?>
        <h2><?= htmlspecialchars((string) $props['title']) ?></h2>
        <?php endif; ?>
        <?php if (!empty($props['text'])): ?>
        <p><?= htmlspecialchars((string) $props['text']) ?></p>
        <?php endif; ?>
        <?php if (!empty($props['buttons']) && is_array($props['buttons'])): ?>
        <div class="hero-links">
            <?php foreach ($props['buttons'] as $btn): ?>
                <?php if (!is_array($btn) || empty($btn['label'])) continue; ?>
                <?php $btnClass = ($btn['style'] ?? 'primary') === 'outline' ? 'btn-outline' : 'btn-primary';
                $rawUrl = $btn['url'] ?? '';
                $url = is_array($rawUrl) ? (string) ($rawUrl['url'] ?? '') : (string) $rawUrl;
                ?>
                <a href="<?= htmlspecialchars(route(ltrim($url, '/'))) ?>" class="btn <?= $btnClass ?>">
                    <?= htmlspecialchars((string) $btn['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
