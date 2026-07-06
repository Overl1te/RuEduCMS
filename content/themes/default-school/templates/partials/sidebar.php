<?php if (!empty($side_menu)): ?>
<aside class="site-sidebar" aria-label="Разделы сайта">
    <nav class="side-nav">
        <p class="side-nav-title">Разделы</p>
        <ul class="side-nav-list">
            <?php foreach ($side_menu as $item): ?>
                <?php include __DIR__ . '/sidebar-item.php'; ?>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>
<?php endif; ?>
