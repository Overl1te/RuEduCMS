<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use RuEdu\Model\Article;

class FieldRenderer
{
    /**
     * @param array<string, mixed> $context
     */
    public static function renderEntity(string $entity, array $context = []): string
    {
        $rows = FieldValueStore::get($entity);
        if ($rows === []) {
            return '';
        }

        if ($entity === 'home') {
            $context['articles'] = $context['articles'] ?? Article::getAll('published', 10);
        }

        return self::renderFlexibleRows($rows, $context);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param array<string, mixed> $context
     */
    public static function renderFlexibleRows(array $rows, array $context = []): string
    {
        $theme = (string) Config::get('theme', 'default-school');
        $themePath = THEMES_PATH . '/' . $theme;
        $html = '';

        foreach ($rows as $row) {
            if (!is_array($row) || empty($row['layout'])) {
                continue;
            }

            $layout = (string) $row['layout'];
            $data = is_array($row['data'] ?? null) ? $row['data'] : [];
            $blockStyle = is_array($row['style'] ?? null) ? $row['style'] : [];
            $elementStyles = is_array($row['elementStyles'] ?? null) ? $row['elementStyles'] : [];
            $blockId = (string) ($row['id'] ?? $layout . '_' . md5(json_encode($row)));

            $props = BlockRegistry::mergeProps($layout, $data);
            $file = $themePath . '/templates/blocks/' . $layout . '.php';

            if (!is_file($file)) {
                continue;
            }

            $site_name = $context['site_name'] ?? Config::get('site_name', '');
            $articles = $context['articles'] ?? [];

            ob_start();
            include $file;
            $blockHtml = (string) ob_get_clean();

            $html .= ElementStyles::wrap($blockId, $blockHtml, $blockStyle, $elementStyles);
        }

        return $html;
    }

    /**
     * @param list<array{type: string, props?: array<string, mixed>}> $blocks
     * @return list<array<string, mixed>>
     */
    public static function legacyBlocksToFieldData(array $blocks): array
    {
        $rows = [];
        foreach ($blocks as $block) {
            if (!is_array($block) || empty($block['type'])) {
                continue;
            }
            $rows[] = [
                'id' => uniqid('blk_', true),
                'layout' => (string) $block['type'],
                'data' => is_array($block['props'] ?? null) ? $block['props'] : [],
                'style' => [],
                'elementStyles' => [],
            ];
        }

        return $rows;
    }

    /**
     * @return array{groups: list<array<string, mixed>>, values: list<array<string, mixed>>, layouts: array<string, array<string, mixed>>}
     */
    public static function getSchemaForEntity(string $entity): array
    {
        $context = FieldValueStore::getContext($entity);
        $groups = FieldGroupEngine::getActiveForContext($context);
        $layouts = [];

        foreach ($groups as $group) {
            foreach ($group['fields'] ?? [] as $field) {
                if (($field['type'] ?? '') === 'flexible' && !empty($field['layouts'])) {
                    $layouts = array_merge($layouts, $field['layouts']);
                }
            }
        }

        return [
            'groups' => $groups,
            'values' => FieldValueStore::get($entity),
            'layouts' => $layouts,
        ];
    }

    /**
     * @param list<array<string, mixed>> $values
     * @return list<array<string, mixed>>
     */
    public static function normalizeFlexibleValues(array $values): array
    {
        $normalized = [];
        foreach ($values as $row) {
            if (!is_array($row) || empty($row['layout'])) {
                continue;
            }
            $layout = (string) $row['layout'];
            $theme = (string) Config::get('theme', 'default-school');
            $templateFile = THEMES_PATH . '/' . $theme . '/templates/blocks/' . $layout . '.php';
            if (BlockRegistry::get($layout) === null && !is_file($templateFile)) {
                continue;
            }

            $normalized[] = [
                'id' => (string) ($row['id'] ?? uniqid('blk_', true)),
                'layout' => $layout,
                'data' => is_array($row['data'] ?? null) ? $row['data'] : [],
                'style' => is_array($row['style'] ?? null) ? $row['style'] : [],
                'elementStyles' => is_array($row['elementStyles'] ?? null) ? $row['elementStyles'] : [],
            ];
        }

        return $normalized;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function defaultHomeFieldData(): array
    {
        return self::normalizeFlexibleValues(array_map(
            static fn (array $b): array => [
                'id' => uniqid('blk_', true),
                'layout' => $b['type'],
                'data' => BlockRegistry::mergeProps($b['type'], []),
                'style' => [],
                'elementStyles' => [],
            ],
            BlockRenderer::defaultHomeLayout()
        ));
    }
}
