<?php $title = 'Формы'; ?>
<h2 class="mb-4">Конструктор форм</h2>
<table class="table table-hover">
    <thead><tr><th>Название</th><th>Slug</th><th>Email</th><th>Статус</th></tr></thead>
    <tbody>
    <?php foreach ($forms as $f): ?>
        <tr>
            <td><?= htmlspecialchars($f['name']) ?></td>
            <td><code><?= htmlspecialchars($f['slug']) ?></code></td>
            <td><?= htmlspecialchars($f['email_to']) ?></td>
            <td><span class="badge bg-<?= $f['status'] === 'active' ? 'success' : 'secondary' ?>"><?= \RuEdu\Engine\Lang::formStatus($f['status']) ?></span></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
