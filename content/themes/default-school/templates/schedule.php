<?php ob_start();
$page_title = 'Расписание занятий';
$page_breadcrumb = 'Расписание';
include __DIR__ . '/partials/page-header.php';
?>
<div class="container page-content">
    <?php if (!empty($classes)): ?>
        <div class="schedule-filter" data-animate>
            <form method="GET">
                <select name="class" onchange="this.form.submit()">
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= htmlspecialchars($c['class_name']) ?>" <?= $currentClass === $c['class_name'] ? 'selected' : '' ?>><?= htmlspecialchars($c['class_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div data-animate data-animate-delay="2" style="overflow-x:auto">
        <table class="schedule-table">
            <thead>
                <tr><th>Урок</th><?php foreach ($days as $d): ?><th><?= $d ?></th><?php endforeach; ?></tr>
            </thead>
            <tbody>
                <?php for ($lesson = 1; $lesson <= 8; $lesson++): ?>
                    <tr>
                        <td><strong><?= $lesson ?></strong></td>
                        <?php foreach (array_keys($days) as $day): ?>
                            <td>
                                <?php if (isset($schedule[$day][$lesson])): ?>
                                    <?php if (!empty($schedule[$day][$lesson]['lesson_time'])): ?>
                                        <small><?= htmlspecialchars($schedule[$day][$lesson]['lesson_time']) ?></small><br>
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($schedule[$day][$lesson]['subject']) ?></strong><br>
                                    <small><?= htmlspecialchars($schedule[$day][$lesson]['teacher']) ?></small><br>
                                    <small>каб. <?= htmlspecialchars($schedule[$day][$lesson]['room']) ?></small>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p data-animate>Расписание пока не добавлено.</p>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
