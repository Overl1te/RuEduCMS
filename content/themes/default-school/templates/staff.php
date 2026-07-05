<?php ob_start(); ?>
<div class="container page-content">
    <h1>Педагогический состав</h1>
    <div class="view-toggle mb-4">
        <a href="<?= route('staff') ?>?view=cards" class="btn <?= $view === 'cards' ? 'btn-primary' : 'btn-outline' ?>">Карточки</a>
        <a href="<?= route('staff') ?>?view=table" class="btn <?= $view === 'table' ? 'btn-primary' : 'btn-outline' ?>">Таблица</a>
    </div>
    <?php if ($view === 'table'): ?>
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
    <?php else: ?>
        <div class="staff-grid">
            <?php foreach ($staff as $s): ?>
                <div class="staff-card">
                    <?php if ($s['photo']): ?>
                        <img src="<?= asset('uploads/' . $s['photo']) ?>" alt="<?= htmlspecialchars($s['name']) ?>">
                    <?php else: ?>
                        <div style="width:120px;height:120px;border-radius:50%;background:#e5e7eb;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:40px;">👤</div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($s['name']) ?></h3>
                    <p class="text-muted"><?= htmlspecialchars($s['position']) ?></p>
                    <?php if ($s['subject']): ?><p><?= htmlspecialchars($s['subject']) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (empty($staff)): ?><p>Информация о педагогическом составе пока не добавлена.</p><?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
