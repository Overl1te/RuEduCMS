<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use RuEdu\Model\Field;
use RuEdu\Model\FieldGroup;

class FieldGroupEngine
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function getActiveForContext(array $context): array
    {
        $result = [];
        foreach (FieldGroup::getAll() as $group) {
            if (empty($group['is_active'])) {
                continue;
            }
            $rules = FieldLocation::parseRules($group['locations'] ?? null);
            if (FieldLocation::matches($rules, $context)) {
                $group['fields'] = self::buildTree((int) $group['id']);
                $result[] = $group;
            }
        }

        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function buildTree(int $groupId, ?int $parentId = null): array
    {
        $rows = Field::getByGroupId($groupId);
        $tree = [];

        foreach ($rows as $row) {
            $rowParent = $row['parent_id'] !== null ? (int) $row['parent_id'] : null;
            if ($rowParent !== $parentId) {
                continue;
            }

            $config = $row['config'] ?? '';
            if (is_string($config) && $config !== '') {
                $decoded = json_decode($config, true);
                $row['config'] = is_array($decoded) ? $decoded : [];
            } elseif (!is_array($config)) {
                $row['config'] = [];
            }

            $id = (int) $row['id'];
            if (FieldTypeRegistry::hasSubfields((string) $row['type'])) {
                $row['subfields'] = self::buildTree($groupId, $id);
                if (($row['type'] ?? '') === 'flexible') {
                    $row['layouts'] = self::buildFlexibleLayouts($row);
                }
            }

            $tree[] = $row;
        }

        return $tree;
    }

    /**
     * @param array<string, mixed> $flexibleField
     * @return array<string, array<string, mixed>>
     */
    private static function buildFlexibleLayouts(array $flexibleField): array
    {
        $layouts = [];
        foreach ($flexibleField['subfields'] ?? [] as $layoutField) {
            $name = (string) ($layoutField['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $config = is_array($layoutField['config'] ?? null) ? $layoutField['config'] : [];
            $layouts[$name] = [
                'label' => (string) ($layoutField['label'] ?? $name),
                'icon' => (string) ($config['icon'] ?? 'bi-square'),
                'template' => (string) ($config['template'] ?? $name),
                'elements' => is_array($config['elements'] ?? null) ? $config['elements'] : [],
                'subfields' => $layoutField['subfields'] ?? [],
            ];
        }

        return $layouts;
    }

    /**
     * @param array<string, mixed> $groupData
     * @param list<array<string, mixed>> $fieldsTree
     */
    public static function saveGroup(array $groupData, array $fieldsTree): int
    {
        $id = (int) ($groupData['id'] ?? 0);
        $locations = $groupData['locations'] ?? [];
        if (is_string($locations)) {
            $locations = json_decode($locations, true) ?: [];
        }

        $payload = [
            'title' => trim((string) ($groupData['title'] ?? '')),
            'slug' => self::slugify(trim((string) ($groupData['slug'] ?? ''))),
            'locations' => json_encode($locations, JSON_UNESCAPED_UNICODE),
            'is_active' => !empty($groupData['is_active']) ? 1 : 0,
            'sort_order' => (int) ($groupData['sort_order'] ?? 0),
        ];

        if ($id > 0) {
            FieldGroup::update($id, $payload);
            Field::deleteByGroupId($id);
        } else {
            $id = FieldGroup::create($payload);
        }

        self::saveFieldsTree($id, $fieldsTree, null);
        Cache::flush();

        return $id;
    }

    /**
     * @param list<array<string, mixed>> $nodes
     */
    private static function saveFieldsTree(int $groupId, array $nodes, ?int $parentId, int $sort = 0): void
    {
        foreach ($nodes as $node) {
            if (!is_array($node) || empty($node['name']) || empty($node['type'])) {
                continue;
            }

            $config = $node['config'] ?? [];
            if (!is_array($config)) {
                $config = [];
            }

            $fieldId = Field::insert([
                'group_id' => $groupId,
                'parent_id' => $parentId,
                'name' => (string) $node['name'],
                'label' => (string) ($node['label'] ?? $node['name']),
                'type' => (string) $node['type'],
                'config' => json_encode($config, JSON_UNESCAPED_UNICODE),
                'sort_order' => $sort++,
            ]);

            $subfields = $node['subfields'] ?? [];
            if (is_array($subfields) && $subfields !== []) {
                self::saveFieldsTree($groupId, $subfields, $fieldId, 0);
            }
        }
    }

    public static function slugify(string $slug): string
    {
        $slug = mb_strtolower($slug);
        $slug = preg_replace('/[^a-z0-9_-]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'field-group';
    }

    public static function seedDefaults(): void
    {
        if (FieldGroup::getBySlug('home-page') === null) {
            $homeLayouts = self::layoutsFromBlockRegistry(['hero', 'stats', 'quick_links', 'latest_news', 'cta'], 'home');
            self::saveGroup([
                'title' => 'Главная страница',
                'slug' => 'home-page',
                'locations' => [['param' => 'page_type', 'operator' => '==', 'value' => 'home']],
                'is_active' => 1,
                'sort_order' => 1,
            ], [[
                'name' => 'content',
                'label' => 'Содержимое',
                'type' => 'flexible',
                'config' => [],
                'subfields' => $homeLayouts,
            ]]);
        }

        if (FieldGroup::getBySlug('default-page') === null) {
            $pageLayouts = self::layoutsFromBlockRegistry(['heading', 'text', 'columns', 'cta', 'spacer'], 'page');
            self::saveGroup([
                'title' => 'Стандартная страница',
                'slug' => 'default-page',
                'locations' => [['param' => 'page_type', 'operator' => '==', 'value' => 'page']],
                'is_active' => 1,
                'sort_order' => 2,
            ], [[
                'name' => 'content',
                'label' => 'Содержимое',
                'type' => 'flexible',
                'config' => [],
                'subfields' => $pageLayouts,
            ]]);
        }

        if (FieldGroup::getBySlug('system-templates') === null) {
            $layouts = self::layoutsFromBlockRegistry(['heading', 'text', 'columns', 'cta', 'spacer', 'hero'], 'page');
            self::saveGroup([
                'title' => 'Системные шаблоны',
                'slug' => 'system-templates',
                'locations' => [['param' => 'page_type', 'operator' => '==', 'value' => 'system']],
                'is_active' => 1,
                'sort_order' => 3,
            ], [[
                'name' => 'content',
                'label' => 'Содержимое',
                'type' => 'flexible',
                'config' => [],
                'subfields' => $layouts,
            ]]);
        }
    }

    /**
     * @param list<string> $layoutNames
     * @return list<array<string, mixed>>
     */
    private static function layoutsFromBlockRegistry(array $layoutNames, string $context): array
    {
        $layouts = [];
        $blocks = BlockRegistry::forContext($context);
        foreach (BlockRegistry::forContext('all') as $type => $meta) {
            $blocks[$type] = $meta;
        }

        foreach ($layoutNames as $name) {
            $block = $blocks[$name] ?? null;
            if ($block === null) {
                continue;
            }

            $subfields = [];
            foreach ($block['fields'] ?? [] as $field) {
                $subfields[] = self::blockFieldToSchema($field, $block['defaults'] ?? []);
            }

            $layouts[] = [
                'name' => $name,
                'label' => (string) ($block['label'] ?? $name),
                'type' => 'group',
                'config' => [
                    'icon' => (string) ($block['icon'] ?? 'bi-square'),
                    'template' => $name,
                    'elements' => self::extractElements($block['fields'] ?? []),
                ],
                'subfields' => $subfields,
            ];
        }

        return $layouts;
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    private static function blockFieldToSchema(array $field, array $defaults): array
    {
        $key = (string) ($field['key'] ?? 'field');
        $type = (string) ($field['type'] ?? 'text');

        if ($type === 'json') {
            return self::jsonFieldToRepeater($key, $field, $defaults);
        }

        $config = ['default' => $defaults[$key] ?? null];
        if ($type === 'select' && !empty($field['options'])) {
            $config['choices'] = $field['options'];
        }
        if (!empty($field['element'])) {
            $config['element'] = $field['element'];
        }

        if ($type === 'textarea' && $key === 'content') {
            $type = 'wysiwyg';
        }
        if (in_array($key, ['title', 'subtitle', 'badge', 'text'], true)) {
            $config['element'] = $key;
        }

        return [
            'name' => $key,
            'label' => (string) ($field['label'] ?? $key),
            'type' => $type,
            'config' => $config,
            'subfields' => [],
        ];
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    private static function jsonFieldToRepeater(string $key, array $field, array $defaults): array
    {
        $sample = $defaults[$key][0] ?? [];

        $subfields = [];
        foreach ($sample as $subKey => $_) {
            $subType = $subKey === 'url' ? 'link' : ($subKey === 'content' ? 'wysiwyg' : 'text');
            if ($subKey === 'style' || $subKey === 'icon') {
                $subType = 'select';
            }
            $subConfig = [];
            if ($subKey === 'style') {
                $subConfig['choices'] = ['primary', 'outline'];
            }
            if ($subKey === 'icon') {
                $subConfig['choices'] = ['docs', 'schedule', 'staff', 'gallery', 'sveden', 'contacts'];
            }
            $subfields[] = [
                'name' => (string) $subKey,
                'label' => (string) $subKey,
                'type' => $subType,
                'config' => $subConfig,
                'subfields' => [],
            ];
        }

        return [
            'name' => $key,
            'label' => (string) ($field['label'] ?? $key),
            'type' => 'repeater',
            'config' => ['default' => $defaults[$key] ?? []],
            'subfields' => $subfields,
        ];
    }

    /**
     * @param list<array<string, mixed>> $fields
     * @return list<string>
     */
    private static function extractElements(array $fields): array
    {
        $elements = [];
        foreach ($fields as $field) {
            if (!empty($field['element'])) {
                $elements[] = (string) $field['element'];
            }
            if (($field['key'] ?? '') === 'title' || ($field['key'] ?? '') === 'subtitle') {
                $elements[] = (string) $field['key'];
            }
        }

        return array_values(array_unique($elements));
    }
}
