<?php
$limit = max(1, min(12, (int) ($props['limit'] ?? 3)));
$newsItems = array_slice(is_array($articles ?? null) ? $articles : [], 0, $limit);
?>
<section class="section news-section">
    <div class="container">
        <div class="section-header" data-animate>
            <?php if (!empty($props['eyebrow'])): ?>
            <span class="section-eyebrow"><?= htmlspecialchars((string) $props['eyebrow']) ?></span>
            <?php endif; ?>
            <?php if (!empty($props['title'])): ?>
            <h2 class="section-title"><?= htmlspecialchars((string) $props['title']) ?></h2>
            <?php endif; ?>
            <?php if (!empty($props['subtitle'])): ?>
            <p class="section-subtitle"><?= htmlspecialchars((string) $props['subtitle']) ?></p>
            <?php endif; ?>
        </div>
        <?php if ($newsItems !== []): ?>
        <div class="news-grid">
            <?php foreach ($newsItems as $i => $article): ?>
                <?php
                $fullText = $article['excerpt'] ?? strip_tags($article['content'] ?? '');
                $excerpt = mb_substr($fullText, 0, 160);
                $delay = min($i + 1, 6);
                ?>
                <article class="news-card" data-animate data-animate-delay="<?= $delay ?>">
                    <div class="news-card__body">
                        <time datetime="<?= $article['published_at'] ?? '' ?>"><?= !empty($article['published_at']) ? date('d.m.Y', strtotime((string) $article['published_at'])) : '' ?></time>
                        <h3><a href="<?= route('news/' . $article['slug']) ?>"><?= htmlspecialchars((string) $article['title']) ?></a></h3>
                        <?php if ($excerpt !== ''): ?>
                        <p><?= htmlspecialchars($excerpt) ?><?= mb_strlen($fullText) > 160 ? '…' : '' ?></p>
                        <?php endif; ?>
                        <a href="<?= route('news/' . $article['slug']) ?>" class="news-card__link">Читать далее →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted text-center" data-animate>Новостей пока нет.</p>
        <?php endif; ?>
        <?php if (!empty($props['show_all_button'])): ?>
        <div class="text-center mt-4" data-animate>
            <a href="<?= route('news') ?>" class="btn btn-primary">Все новости →</a>
        </div>
        <?php endif; ?>
    </div>
</section>
