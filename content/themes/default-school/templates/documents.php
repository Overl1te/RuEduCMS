<?php ob_start(); ?>
<div class="container page-content">
    <h1>Документы</h1>
    <?php foreach ($documents as $category => $docs): ?>
        <h2 class="mt-4"><?= htmlspecialchars($category) ?></h2>
        <ul class="doc-list">
            <?php foreach ($docs as $doc): ?>
                <li>
                    <a href="<?= asset('uploads/' . $doc['file_path']) ?>" target="_blank"><?= htmlspecialchars($doc['title']) ?></a>
                    <?php if ($doc['published_at']): ?><span><?= date('d.m.Y', strtotime($doc['published_at'])) ?></span><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
    <?php if (empty($documents)): ?><p>Документы пока не добавлены.</p><?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
