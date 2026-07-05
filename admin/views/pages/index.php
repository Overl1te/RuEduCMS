<?php
use RuEdu\Engine\Auth;

$title = 'Страницы';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Страницы</h2>
    <a href="<?= url('admin/pages/create') ?>" class="btn btn-primary"><i class="bi bi-plus"></i> Создать</a>
</div>

<?php if (!empty($systemPages)): ?>
<h5 class="text-muted mb-3">Системные страницы</h5>
<table class="table table-hover mb-4">
    <thead><tr><th>Название</th><th>URL</th><th>Статус</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($systemPages as $p): ?>
        <tr>
            <td>
                <?= htmlspecialchars($p['title']) ?>
                <span class="badge bg-info text-dark ms-1">Системная</span>
            </td>
            <td><code><?= htmlspecialchars($p['url']) ?></code></td>
            <td>
                <?php if (!($p['enabled'] ?? true)): ?>
                    <span class="badge bg-secondary">Модуль отключён</span>
                <?php else: ?>
                    <span class="badge bg-success">Опубликована</span>
                <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
                <a href="<?= route($p['url']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Открыть на сайте">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <?php if (!empty($p['content_url'])): ?>
                    <a href="<?= url($p['content_url']) ?>" class="btn btn-sm btn-outline-primary" title="Редактировать содержимое">
                        <i class="bi bi-pencil"></i>
                    </a>
                <?php endif; ?>
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= url('admin/themes/edit/' . rawurlencode($activeTheme) . '?file=' . rawurlencode($p['template'])) ?>"
                       class="btn btn-sm btn-outline-primary" title="Редактировать шаблон">
                        <i class="bi bi-code-slash"></i>
                    </a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<h5 class="text-muted mb-3">Пользовательские страницы</h5>
<?php if (empty($pages)): ?>
    <div class="alert alert-light border">Пользовательских страниц пока нет. Создайте первую через кнопку «Создать».</div>
<?php else: ?>
<table class="table table-hover">
    <thead><tr><th>Название</th><th>URL</th><th>Статус</th><th>Дата</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($pages as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['title']) ?></td>
            <td><code>/page/<?= htmlspecialchars($p['slug']) ?></code></td>
            <td><span class="badge bg-<?= $p['status'] === 'published' ? 'success' : 'secondary' ?>"><?= \RuEdu\Engine\Lang::publishStatus($p['status']) ?></span></td>
            <td><?= date('d.m.Y', strtotime($p['updated_at'])) ?></td>
            <td class="text-end">
                <a href="<?= route('/page/' . $p['slug']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Открыть на сайте">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <a href="<?= url('admin/pages/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="<?= url('admin/pages/delete/' . $p['id']) ?>" class="d-inline" onsubmit="return confirm('Удалить?')">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
