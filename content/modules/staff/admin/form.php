<?php $title = $member ? 'Редактирование' : 'Новый сотрудник'; ?>
<h2 class="mb-4"><?= $title ?></h2>
<form method="POST" action="<?= url('admin/staff/save') ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
    <?php if ($member): ?><input type="hidden" name="id" value="<?= $member['id'] ?>"><?php endif; ?>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3"><label class="form-label">ФИО</label><input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($member['name'] ?? '') ?>"></div>
            <div class="mb-3"><label class="form-label">Должность</label><input type="text" name="position" class="form-control" required value="<?= htmlspecialchars($member['position'] ?? '') ?>"></div>
            <div class="mb-3"><label class="form-label">Предмет</label><input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($member['subject'] ?? '') ?>"></div>
            <div class="mb-3"><label class="form-label">Образование</label><textarea name="education" class="form-control" rows="2"><?= htmlspecialchars($member['education'] ?? '') ?></textarea></div>
            <div class="mb-3"><label class="form-label">Квалификация</label><input type="text" name="qualification" class="form-control" value="<?= htmlspecialchars($member['qualification'] ?? '') ?>"></div>
            <div class="mb-3"><label class="form-label">Стаж</label><input type="text" name="experience" class="form-control" value="<?= htmlspecialchars($member['experience'] ?? '') ?>"></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($member['email'] ?? '') ?>"></div>
            <div class="mb-3"><label class="form-label">Телефон</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($member['phone'] ?? '') ?>"></div>
            <div class="mb-3"><label class="form-label">Порядок</label><input type="number" name="sort_order" class="form-control" value="<?= $member['sort_order'] ?? 0 ?>"></div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </div>
</form>
