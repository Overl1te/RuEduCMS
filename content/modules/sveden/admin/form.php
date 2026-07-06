<?php $title = $sectionTitle ?? $section; ?>
<h2 class="mb-4"><?= htmlspecialchars($sectionTitle ?? $section) ?></h2>
<form method="POST" action="<?= url('admin/sveden/save') ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">
    <?php foreach ($fields as $key => $label): ?>
        <div class="mb-3">
            <label class="form-label"><?= htmlspecialchars($label) ?></label>
            <?php if (strlen($label) > 50 || in_array($key, ['programs', 'info', 'content', 'local_acts'])): ?>
                <textarea name="<?= $key ?>" class="form-control" rows="4"><?= htmlspecialchars($data[$key] ?? '') ?></textarea>
            <?php elseif ($key === 'phone'): ?>
                <input type="tel" name="<?= $key ?>" class="form-control" value="<?= htmlspecialchars($data[$key] ?? '') ?>" placeholder="+7 (___) ___-__-__">
            <?php else: ?>
                <input type="text" name="<?= $key ?>" class="form-control" value="<?= htmlspecialchars($data[$key] ?? '') ?>">
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="<?= url('admin/sveden') ?>" class="btn btn-outline-secondary">Назад</a>
</form>
