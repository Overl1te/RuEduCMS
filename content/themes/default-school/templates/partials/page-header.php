<?php
/** @var string $page_title */
/** @var string|null $page_breadcrumb */
?>
<section class="page-hero">
    <div class="container page-hero__inner">
        <?php if (!empty($page_breadcrumb)): ?>
            <nav class="page-hero__breadcrumb" aria-label="Навигация">
                <a href="<?= route('') ?>">Главная</a>
                <span aria-hidden="true">/</span>
                <span><?= htmlspecialchars($page_breadcrumb) ?></span>
            </nav>
        <?php endif; ?>
        <h1><?= htmlspecialchars($page_title) ?></h1>
    </div>
</section>
