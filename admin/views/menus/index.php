<?php $title = 'Меню'; ?>
<h2 class="mb-4">Управление меню</h2>
<form method="POST" action="<?= url('admin/menus/save') ?>" id="menuForm">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="menu_id" value="<?= $menuId ?>">
    <input type="hidden" name="items" id="menuItems" value="">
    <div id="menuList">
        <?php foreach ($items as $i => $item): ?>
            <div class="input-group mb-2 menu-item">
                <input type="text" class="form-control" placeholder="Название" value="<?= htmlspecialchars($item['title']) ?>" data-field="title">
                <input type="text" class="form-control" placeholder="URL" value="<?= htmlspecialchars($item['url']) ?>" data-field="url">
                <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()"><i class="bi bi-trash"></i></button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="addMenuItem()"><i class="bi bi-plus"></i> Пункт</button>
    <br>
    <button type="submit" class="btn btn-primary" onclick="collectMenuItems()">Сохранить меню</button>
</form>
<script>
function addMenuItem() {
    const div = document.createElement('div');
    div.className = 'input-group mb-2 menu-item';
    div.innerHTML = '<input type="text" class="form-control" placeholder="Название" data-field="title">' +
        '<input type="text" class="form-control" placeholder="URL" data-field="url">' +
        '<button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()"><i class="bi bi-trash"></i></button>';
    document.getElementById('menuList').appendChild(div);
}
function collectMenuItems() {
    const items = [];
    document.querySelectorAll('.menu-item').forEach(el => {
        items.push({
            title: el.querySelector('[data-field=title]').value,
            url: el.querySelector('[data-field=url]').value
        });
    });
    document.getElementById('menuItems').value = JSON.stringify(items);
}
</script>
