<?php

use RuEdu\Engine\YandexMap;
use RuEdu\Model\Setting;

$rawMap = trim((string) ($yandex_map_raw ?? Setting::get('yandex_map', '')));
$mapHtml = $rawMap !== '' ? YandexMap::embedHtml($rawMap) : ($yandex_map ?? '');
$showMapEmpty = !empty($show_map_empty);

if ($mapHtml !== ''): ?>
    <div class="map-container"><?= $mapHtml ?></div>
<?php elseif ($showMapEmpty): ?>
    <div class="map-container map-container--empty">
        <p>Карта не настроена. Добавьте код iframe или ссылку на карту в <a href="<?= url('admin/settings') ?>">настройках сайта</a>.</p>
    </div>
<?php endif; ?>
