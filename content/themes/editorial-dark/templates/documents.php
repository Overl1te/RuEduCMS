<?php ob_start();
$page_title = 'Документы';
$page_breadcrumb = 'Документы';
include __DIR__ . '/partials/page-header.php';
?>
<div class="ed-container ed-page-content">
    <?php foreach ($documents as $category => $docs): ?>
        <h2 class="ed-mt-4"><?= htmlspecialchars($category) ?></h2>
        <ul class="ed-doc-list">
            <?php foreach ($docs as $doc): ?>
                <li>
                    <a href="<?= asset('uploads/' . $doc['file_path']) ?>" target="_blank"><?= htmlspecialchars($doc['title']) ?></a>
                    <?php if ($doc['published_at']): ?><span class="ed-muted"><?= date('d.m.Y', strtotime($doc['published_at'])) ?></span><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
    <?php if (empty($documents)): ?><p class="ed-muted">Документы пока не добавлены.</p><?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
