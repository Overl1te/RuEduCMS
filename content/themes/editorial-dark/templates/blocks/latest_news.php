<?php
$limit = max(1, min(12, (int) ($props['limit'] ?? 3)));
$newsItems = array_slice(is_array($articles ?? null) ? $articles : [], 0, $limit);
?>
<section class="ed-section">
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
        <?php if ($newsItems !== []): ?>
        <div class="ed-news-feed">
            <?php foreach ($newsItems as $article): ?>
                <?php
                $fullText = $article['excerpt'] ?? strip_tags($article['content'] ?? '');
                $excerpt = mb_substr($fullText, 0, 160);
                ?>
                <article class="ed-news-item">
                    <time datetime="<?= $article['published_at'] ?? '' ?>"><?= !empty($article['published_at']) ? date('d.m.Y', strtotime((string) $article['published_at'])) : '' ?></time>
                    <h3><a href="<?= route('news/' . $article['slug']) ?>"><?= htmlspecialchars((string) $article['title']) ?></a></h3>
                    <?php if ($excerpt !== ''): ?>
                    <p><?= htmlspecialchars($excerpt) ?><?= mb_strlen($fullText) > 160 ? '…' : '' ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="ed-muted">Новостей пока нет.</p>
        <?php endif; ?>
        <?php if (!empty($props['show_all_button'])): ?>
        <p class="ed-section__footer">
            <a href="<?= route('news') ?>" class="ed-btn ed-btn--primary">Все новости</a>
        </p>
        <?php endif; ?>
    </div>
</section>
