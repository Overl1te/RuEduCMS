<?php $title = 'Расписание'; ?>
<div class="d-flex justify-content-between mb-4">
    <h2>Расписание</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus"></i> Добавить</button>
</div>
<div class="mb-3">
    <form method="GET" class="d-flex gap-2">
        <select name="class" class="form-select" style="width:200px" onchange="this.form.submit()">
            <option value="">Все классы</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['class_name']) ?>" <?= $class === $c['class_name'] ? 'selected' : '' ?>><?= htmlspecialchars($c['class_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<table class="table table-sm table-bordered">
    <thead><tr><th>Класс</th><th>День</th><th>Урок</th><th>Предмет</th><th>Учитель</th><th>Кабинет</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['class_name']) ?></td>
            <td><?= $days[$item['day_of_week']] ?? $item['day_of_week'] ?></td>
            <td><?= $item['lesson_number'] ?></td>
            <td><?= htmlspecialchars($item['subject']) ?></td>
            <td><?= htmlspecialchars($item['teacher']) ?></td>
            <td><?= htmlspecialchars($item['room']) ?></td>
            <td>
                <form method="POST" action="<?= url('admin/schedule/delete/' . $item['id']) ?>" class="d-inline" onsubmit="return confirm('Удалить?')">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog"><form method="POST" action="<?= url('admin/schedule/save') ?>" class="modal-content">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="modal-header"><h5 class="modal-title">Добавить урок</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label">Класс</label><input type="text" name="class_name" class="form-control" required placeholder="5А"></div>
            <div class="mb-2"><label class="form-label">День</label><select name="day_of_week" class="form-select"><?php foreach ($days as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select></div>
            <div class="mb-2"><label class="form-label">Номер урока</label><input type="number" name="lesson_number" class="form-control" min="1" max="10" value="1"></div>
            <div class="mb-2"><label class="form-label">Предмет</label><input type="text" name="subject" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Учитель</label><input type="text" name="teacher" class="form-control"></div>
            <div class="mb-2"><label class="form-label">Кабинет</label><input type="text" name="room" class="form-control"></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Сохранить</button></div>
    </form></div>
</div>
