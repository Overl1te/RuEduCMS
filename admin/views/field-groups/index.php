<?php $title = 'Группы полей'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Группы полей</h2>
    <a href="<?= url('admin/field-groups/edit/0') ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Создать</a>
</div>
<table class="table table-hover">
    <thead>
        <tr>
            <th>Название</th>
            <th>Slug</th>
            <th>Правила</th>
            <th>Статус</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($groups as $g): ?>
        <?php
        $rules = \RuEdu\Engine\FieldLocation::parseRules($g['locations'] ?? null);
        $rulesText = implode(', ', array_map(
            static fn ($r) => ($r['param'] ?? '') . ' ' . ($r['operator'] ?? '') . ' ' . ($r['value'] ?? ''),
            $rules
        ));
        ?>
        <tr>
            <td><?= htmlspecialchars($g['title'] ?? '') ?></td>
            <td><code><?= htmlspecialchars($g['slug'] ?? '') ?></code></td>
            <td class="small text-muted"><?= htmlspecialchars($rulesText) ?></td>
            <td>
                <span class="badge bg-<?= !empty($g['is_active']) ? 'success' : 'secondary' ?>">
                    <?= !empty($g['is_active']) ? 'Активна' : 'Выкл.' ?>
                </span>
            </td>
            <td class="text-end text-nowrap">
                <a href="<?= url('admin/field-groups/edit/' . (int) $g['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="<?= url('admin/field-groups/delete/' . (int) $g['id']) ?>" class="d-inline" onsubmit="return confirm('Удалить группу?');">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php if ($groups === []): ?>
<div class="alert alert-info">Группы полей не найдены. Будут созданы автоматически при обновлении до версии 0.0.14.</div>
<?php endif; ?>
