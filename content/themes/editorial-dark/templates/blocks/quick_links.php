<section class="ed-section ed-section--alt">
    <div class="ed-container">
        <header class="ed-section__header">
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
        <?php if (!empty($props['links']) && is_array($props['links'])): ?>
        <ol class="ed-link-list">
            <?php $num = 0; foreach ($props['links'] as $link): ?>
                <?php
                if (!is_array($link) || empty($link['label'])) {
                    continue;
                }
                $url = (string) ($link['url'] ?? '');
                if ($url !== '' && !\RuEdu\Engine\Modules::isUrlEnabled($url)) {
                    continue;
                }
                $num++;
                ?>
                <li>
                    <a href="<?= htmlspecialchars(route(ltrim($url, '/'))) ?>">
                        <span class="ed-link-list__num"><?= str_pad((string) $num, 2, '0', STR_PAD_LEFT) ?></span>
                        <?= htmlspecialchars((string) $link['label']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
        <?php endif; ?>
    </div>
</section>
