<?php ob_start();
$page_title = 'Педагогический состав';
$page_breadcrumb = 'Педагоги';
include __DIR__ . '/partials/page-header.php';
?>
<div class="ed-container ed-page-content">
    <div class="ed-view-toggle ed-mb-4">
        <a href="<?= route('staff') ?>?view=cards" class="ed-btn <?= $view === 'cards' ? 'ed-btn--primary' : 'ed-btn--outline' ?>">Карточки</a>
        <a href="<?= route('staff') ?>?view=table" class="ed-btn <?= $view === 'table' ? 'ed-btn--primary' : 'ed-btn--outline' ?>">Таблица</a>
    </div>
    <?php if ($view === 'table'): ?>
        <div class="ed-table-wrap">
        <table class="ed-table ed-staff-table">
            <thead><tr><th>ФИО</th><th>Должность</th><th>Предмет</th><th>Образование</th><th>Стаж</th></tr></thead>
            <tbody>
            <?php foreach ($staff as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['name']) ?></td>
                    <td><?= htmlspecialchars($s['position']) ?></td>
                    <td><?= htmlspecialchars($s['subject']) ?></td>
                    <td><?= htmlspecialchars($s['education']) ?></td>
                    <td><?= htmlspecialchars($s['experience']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <div class="ed-staff-grid">
            <?php foreach ($staff as $s): ?>
                <div class="ed-staff-card">
                    <?php if ($s['photo']): ?>
                        <img src="<?= asset('uploads/' . $s['photo']) ?>" alt="<?= htmlspecialchars($s['name']) ?>">
                    <?php else: ?>
                        <div class="ed-staff-card__avatar" aria-hidden="true">—</div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($s['name']) ?></h3>
                    <p class="ed-muted"><?= htmlspecialchars($s['position']) ?></p>
                    <?php if ($s['subject']): ?><p><?= htmlspecialchars($s['subject']) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (empty($staff)): ?><p class="ed-muted">Список педагогов пока пуст.</p><?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
