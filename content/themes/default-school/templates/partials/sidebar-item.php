<li class="side-nav-item<?= !empty($item['children']) ? ' has-children' : '' ?>">
    <a href="<?= htmlspecialchars($item['url']) ?>"
       <?= ($item['target'] ?? '_self') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
        <?= htmlspecialchars($item['title']) ?>
    </a>
    <?php if (!empty($item['children'])): ?>
        <ul class="side-nav-sublist">
            <?php foreach ($item['children'] as $child): ?>
                <?php $item = $child; include __DIR__ . '/sidebar-item.php'; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>
