<ul class="sitemap-tree">
    <?php foreach ($items as $node): ?>
        <li class="sitemap-tree-item">
            <?php if (($node['url'] ?? '#') !== '#'): ?>
                <a href="<?= htmlspecialchars(route($node['url'])) ?>"><?= htmlspecialchars($node['title']) ?></a>
            <?php else: ?>
                <span class="sitemap-tree-label"><?= htmlspecialchars($node['title']) ?></span>
            <?php endif; ?>
            <?php if (!empty($node['children'])): ?>
                <?php $items = $node['children']; include __DIR__ . '/sitemap-tree.php'; ?>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
