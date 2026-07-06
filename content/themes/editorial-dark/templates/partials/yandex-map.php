<?php

use RuEdu\Engine\YandexMap;
use RuEdu\Model\Setting;

$rawMap = trim((string) ($yandex_map_raw ?? Setting::get('yandex_map', '')));
$mapHtml = $rawMap !== '' ? YandexMap::embedHtml($rawMap) : ($yandex_map ?? '');
$showMapEmpty = !empty($show_map_empty);

if ($mapHtml !== ''): ?>
    <div class="ed-map">
        <div class="ed-map__embed"><?= $mapHtml ?></div>
    </div>
<?php elseif ($showMapEmpty): ?>
    <div class="ed-map ed-map--empty">
        <p>Карта не настроена. Добавьте код iframe или ссылку на карту в <a href="<?= url('admin/settings') ?>">настройках сайта</a>.</p>
    </div>
<?php endif; ?>
