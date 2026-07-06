<?php
/** @var string $page_title */
/** @var string|null $page_breadcrumb */
?>
<section class="ed-page-header">
    <div class="ed-container">
        <?php if (!empty($page_breadcrumb)): ?>
            <nav class="ed-breadcrumb" aria-label="Навигация">
                <a href="<?= route('') ?>">Главная</a>
                <span aria-hidden="true">—</span>
                <span><?= htmlspecialchars($page_breadcrumb) ?></span>
            </nav>
        <?php endif; ?>
        <h1 class="ed-page-header__title"><?= htmlspecialchars($page_title) ?></h1>
        <hr class="ed-rule">
    </div>
</section>
