<?php

declare(strict_types=1);

use RuEdu\Engine\BlockRenderer;
use RuEdu\Engine\FieldRenderer;
use RuEdu\Engine\FieldValueStore;
use RuEdu\Model\Setting;

/**
 * Сид данных главной страницы для групп полей (home_field_data).
 */
return static function (\PDO $pdo, string $prefix): void {
    $homeKey = FieldValueStore::HOME_KEY;
    $homeData = Setting::get($homeKey, '');
    if (is_string($homeData) && $homeData !== '') {
        return;
    }

    $legacy = Setting::get(BlockRenderer::HOME_LAYOUT_KEY, '');
    if (is_string($legacy) && $legacy !== '') {
        $decoded = json_decode($legacy, true);
        if (is_array($decoded) && $decoded !== []) {
            $migrated = FieldRenderer::legacyBlocksToFieldData(
                BlockRenderer::normalizeBlocks($decoded)
            );
            Setting::set($homeKey, json_encode($migrated, JSON_UNESCAPED_UNICODE), 'theme');

            return;
        }
    }

    $default = FieldRenderer::defaultHomeFieldData();
    Setting::set($homeKey, json_encode($default, JSON_UNESCAPED_UNICODE), 'theme');
};
