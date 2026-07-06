<section class="hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="hero-orb hero-orb--1" aria-hidden="true" data-parallax="0.15"></div>
    <div class="hero-orb hero-orb--2" aria-hidden="true" data-parallax="0.1"></div>
    <div class="hero-orb hero-orb--3" aria-hidden="true" data-parallax="0.2"></div>
    <div class="container hero-inner">
        <?php if (!empty($props['badge'])): ?>
        <p class="hero-badge" data-fg-element="badge"><?= htmlspecialchars((string) $props['badge']) ?></p>
        <?php endif; ?>
        <h1 data-fg-element="title"><?= htmlspecialchars((string) (($props['title'] ?? '') !== '' ? $props['title'] : $site_name)) ?></h1>
        <?php if (!empty($props['subtitle'])): ?>
        <p class="hero-subtitle" data-fg-element="subtitle"><?= htmlspecialchars((string) $props['subtitle']) ?></p>
        <?php endif; ?>
        <?php if (!empty($props['buttons']) && is_array($props['buttons'])): ?>
        <div class="hero-links">
            <?php foreach ($props['buttons'] as $btn): ?>
                <?php if (!is_array($btn) || empty($btn['label'])) continue; ?>
                <?php $btnClass = ($btn['style'] ?? 'primary') === 'outline' ? 'btn-outline' : 'btn-primary'; ?>
                <?php
                $rawUrl = $btn['url'] ?? '';
                $url = is_array($rawUrl) ? (string) ($rawUrl['url'] ?? '') : (string) $rawUrl;
                ?>
                <a href="<?= htmlspecialchars(route(ltrim($url, '/'))) ?>" class="btn <?= $btnClass ?>" data-fg-element="button">
                    <?= htmlspecialchars((string) $btn['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
