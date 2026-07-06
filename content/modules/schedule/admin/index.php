<?php $title = 'Расписание'; ?>
<h2 class="mb-4">Расписание</h2>

<div class="schedule-class-grid">
    <?php foreach ($classes as $c): ?>
        <?php $name = $c['class_name']; ?>
        <a href="<?= url('admin/schedule?class=' . urlencode($name)) ?>"
           class="schedule-class-cell<?= $class === $name ? ' schedule-class-cell--active' : '' ?>">
            <?= htmlspecialchars($name) ?>
        </a>
    <?php endforeach; ?>
    <button type="button"
            class="schedule-class-cell schedule-class-cell--add"
            data-bs-toggle="modal"
            data-bs-target="#classModal"
            title="Добавить класс">
        <i class="bi bi-plus-lg"></i>
    </button>
</div>

<?php if ($class): ?>
    <?php foreach ($days as $dayNum => $dayName): ?>
        <?php
        $dayLessons = $schedule[$dayNum] ?? [];
        ksort($dayLessons);
        $nextLesson = $dayLessons ? max(array_keys($dayLessons)) + 1 : 1;
        ?>
        <h5 class="schedule-day-title"><?= htmlspecialchars($dayName) ?></h5>
        <table class="table table-sm table-bordered schedule-day-table">
            <thead>
                <tr>
                    <th>Урок</th>
                    <th>Предмет</th>
                    <th>Учитель</th>
                    <th>Кабинет</th>
                    <th style="width:48px"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dayLessons as $lesson): ?>
                    <tr>
                        <td><?= (int) $lesson['lesson_number'] ?></td>
                        <td><?= htmlspecialchars($lesson['subject']) ?></td>
                        <td><?= htmlspecialchars($lesson['teacher']) ?></td>
                        <td><?= htmlspecialchars($lesson['room']) ?></td>
                        <td>
                            <form method="POST" action="<?= url('admin/schedule/delete/' . $lesson['id']) ?>" class="d-inline" onsubmit="return confirm('Удалить?')">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="schedule-add-row">
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-outline-primary schedule-add-lesson"
                                data-bs-toggle="modal"
                                data-bs-target="#lessonModal"
                                data-day="<?= (int) $dayNum ?>"
                                data-lesson="<?= (int) $nextLesson ?>">
                            <i class="bi bi-plus"></i>
                        </button>
                    </td>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
    <?php endforeach; ?>
<?php endif; ?>

<div class="modal fade" id="classModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('admin/schedule/class/save') ?>" class="modal-content">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="modal-header">
                <h5 class="modal-title">Добавить класс</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Название класса</label>
                    <input type="text" name="class_name" class="form-control" required placeholder="5А">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="lessonModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('admin/schedule/save') ?>" class="modal-content" id="lessonForm">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="class_name" value="<?= htmlspecialchars($class) ?>">
            <input type="hidden" name="day_of_week" id="lessonDay" value="">
            <div class="modal-header">
                <h5 class="modal-title">Добавить урок</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Номер урока</label>
                    <input type="number" name="lesson_number" id="lessonNumber" class="form-control" min="1" max="10" value="1" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Предмет</label>
                    <input type="text" name="subject" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Учитель</label>
                    <input type="text" name="teacher" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Кабинет</label>
                    <input type="text" name="room" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('lessonModal')?.addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    if (!btn) return;
    document.getElementById('lessonDay').value = btn.getAttribute('data-day') || '1';
    document.getElementById('lessonNumber').value = btn.getAttribute('data-lesson') || '1';
});
</script>
