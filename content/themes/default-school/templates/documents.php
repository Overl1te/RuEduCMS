<?php ob_start();
$page_title = 'Документы';
$page_breadcrumb = 'Документы';
include __DIR__ . '/partials/page-header.php';
?>
<div class="container page-content">
    <?php foreach ($documents as $category => $docs): ?>
        <h2 class="mt-4" data-animate><?= htmlspecialchars($category) ?></h2>
        <ul class="doc-list">
            <?php foreach ($docs as $i => $doc): ?>
                <li data-animate data-animate-delay="<?= min(($i % 6) + 1, 6) ?>">
                    <a href="<?= asset('uploads/' . $doc['file_path']) ?>" target="_blank"><?= htmlspecialchars($doc['title']) ?></a>
                    <?php if ($doc['published_at']): ?><span class="text-muted"><?= date('d.m.Y', strtotime($doc['published_at'])) ?></span><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
    <?php if (empty($documents)): ?><p data-animate>Документы пока не добавлены.</p><?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
