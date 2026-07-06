<?php ob_start();
$page_title = 'Педагогический состав';
$page_breadcrumb = 'Педагоги';
include __DIR__ . '/partials/page-header.php';
?>
<div class="container page-content">
    <div class="view-toggle mb-4" data-animate>
        <a href="<?= route('staff') ?>?view=cards" class="btn <?= $view === 'cards' ? 'btn-primary' : 'btn-outline' ?>">Карточки</a>
        <a href="<?= route('staff') ?>?view=table" class="btn <?= $view === 'table' ? 'btn-primary' : 'btn-outline' ?>">Таблица</a>
    </div>
    <?php if ($view === 'table'): ?>
        <div data-animate>
        <table class="staff-table">
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
        <div class="staff-grid">
            <?php foreach ($staff as $i => $s): ?>
                <div class="staff-card" data-animate data-animate-delay="<?= min(($i % 6) + 1, 6) ?>">
                    <?php if ($s['photo']): ?>
                        <img src="<?= asset('uploads/' . $s['photo']) ?>" alt="<?= htmlspecialchars($s['name']) ?>">
                    <?php else: ?>
                        <div class="staff-card__avatar" aria-hidden="true">👤</div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($s['name']) ?></h3>
                    <p class="text-muted"><?= htmlspecialchars($s['position']) ?></p>
                    <?php if ($s['subject']): ?><p><?= htmlspecialchars($s['subject']) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (empty($staff)): ?><p data-animate>Информация о педагогическом составе пока не добавлена.</p><?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
