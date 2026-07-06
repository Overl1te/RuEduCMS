<?php

use RuEdu\Engine\YandexMap;

$mapHtml = $yandex_map ?? YandexMap::fromSettings();
$showMapEmpty = !empty($show_map_empty);

if ($mapHtml !== ''): ?>
    <div class="map-container"><?= $mapHtml ?></div>
<?php elseif ($showMapEmpty): ?>
    <div class="map-container map-container--empty">
        <p>Карта не настроена. Добавьте код iframe или ссылку на карту в <a href="<?= url('admin/settings') ?>">настройках сайта</a>.</p>
    </div>
<?php endif; ?>
