<?php $title = 'Сведения об ОО'; ?>
<h2 class="mb-4">Сведения об образовательной организации</h2>
<p class="text-muted mb-4">Заполните обязательные разделы в соответствии с требованиями Минобрнауки РФ.</p>
<div class="row g-3">
    <?php foreach ($sectionList as $key => $label): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1"><?= htmlspecialchars($label) ?></h6>
                        <?php if (!empty($sections[$key])): ?>
                            <small class="text-success"><i class="bi bi-check-circle"></i> Заполнено</small>
                        <?php else: ?>
                            <small class="text-warning"><i class="bi bi-exclamation-circle"></i> Не заполнено</small>
                        <?php endif; ?>
                    </div>
                    <a href="<?= url('admin/sveden/edit/' . $key) ?>" class="btn btn-sm btn-outline-primary">Редактировать</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<p class="mt-4"><a href="<?= route('sveden') ?>" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-eye"></i> Просмотреть на сайте</a></p>
