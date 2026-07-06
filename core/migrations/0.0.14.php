<?php

declare(strict_types=1);

use RuEdu\Engine\FieldGroupEngine;
use RuEdu\Engine\FieldRenderer;
use RuEdu\Engine\FieldValueStore;
use RuEdu\Engine\BlockRenderer;
use RuEdu\Engine\Migrate;
use RuEdu\Model\Setting;

/**
 * Сид field groups и миграция legacy home_layout / content_blocks.
 */
return static function (\PDO $pdo, string $prefix): void {
    FieldGroupEngine::seedDefaults();

    $homeKey = FieldValueStore::HOME_KEY;
    $homeData = Setting::get($homeKey, '');
    if ($homeData === '' || $homeData === null) {
        $legacy = Setting::get(BlockRenderer::HOME_LAYOUT_KEY, '');
        if (is_string($legacy) && $legacy !== '') {
            $decoded = json_decode($legacy, true);
            if (is_array($decoded) && $decoded !== []) {
                $migrated = FieldRenderer::legacyBlocksToFieldData(
                    BlockRenderer::normalizeBlocks($decoded)
                );
                Setting::set($homeKey, json_encode($migrated, JSON_UNESCAPED_UNICODE), 'theme');
            }
        }
    }

    $pages = $prefix . 'pages';
    if (!Migrate::tableExists($pdo, $pages) || !Migrate::columnExists($pdo, $pages, 'field_data')) {
        return;
    }

    $stmt = $pdo->query("SELECT id, content_mode, content_blocks, field_data FROM `{$pages}` WHERE content_mode = 'blocks' AND content_blocks IS NOT NULL AND (field_data IS NULL OR field_data = 'null')");
    if ($stmt === false) {
        return;
    }

    $update = $pdo->prepare("UPDATE `{$pages}` SET field_data = ?, content_mode = 'fields' WHERE id = ?");
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $blocks = json_decode((string) ($row['content_blocks'] ?? ''), true);
        if (!is_array($blocks)) {
            continue;
        }
        $fieldData = FieldRenderer::legacyBlocksToFieldData(BlockRenderer::normalizeBlocks($blocks));
        $update->execute([json_encode($fieldData, JSON_UNESCAPED_UNICODE), (int) $row['id']]);
    }
};
