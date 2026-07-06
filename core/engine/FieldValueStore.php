<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use RuEdu\Model\Page;
use RuEdu\Model\Setting;

class FieldValueStore
{
    public const HOME_KEY = 'home_field_data';

    public static function parseEntity(string $entity): array
    {
        if ($entity === 'home') {
            return ['type' => 'home', 'id' => 'home'];
        }
        if (str_starts_with($entity, 'page:')) {
            return ['type' => 'page', 'id' => (int) substr($entity, 5)];
        }
        if (str_starts_with($entity, 'system:')) {
            return ['type' => 'system', 'id' => substr($entity, 7)];
        }

        return ['type' => '', 'id' => ''];
    }

    public static function entityKey(string $type, int|string $id): string
    {
        return $type . ':' . $id;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function get(string $entity): array
    {
        $parsed = self::parseEntity($entity);

        return match ($parsed['type']) {
            'home' => self::decode(Setting::get(self::HOME_KEY, '')),
            'page' => self::getPageData((int) $parsed['id']),
            'system' => self::decode(Setting::get('system_field_data_' . $parsed['id'], '')),
            default => [],
        };
    }

    /**
     * @param list<array<string, mixed>> $data
     */
    public static function save(string $entity, array $data): void
    {
        $parsed = self::parseEntity($entity);
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        match ($parsed['type']) {
            'home' => Setting::set(self::HOME_KEY, $json, 'theme'),
            'page' => Page::update((int) $parsed['id'], [
                'field_data' => $json,
                'content_mode' => 'fields',
            ]),
            'system' => Setting::set('system_field_data_' . $parsed['id'], $json, 'theme'),
            default => null,
        };

        Cache::flush();
    }

    /**
     * @return array<string, mixed>
     */
    public static function getContext(string $entity): array
    {
        $parsed = self::parseEntity($entity);

        if ($parsed['type'] === 'home') {
            return ['page_type' => 'home'];
        }
        if ($parsed['type'] === 'page') {
            $page = Page::getById((int) $parsed['id']);

            return [
                'page_type' => 'page',
                'page_slug' => $page['slug'] ?? '',
            ];
        }
        if ($parsed['type'] === 'system') {
            return [
                'page_type' => 'system',
                'system_page' => (string) $parsed['id'],
            ];
        }

        return [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function getPageData(int $id): array
    {
        $page = Page::getById($id);
        if (!$page) {
            return [];
        }

        if (!empty($page['field_data'])) {
            return self::decode($page['field_data']);
        }

        if (($page['content_mode'] ?? '') === 'blocks' && !empty($page['content_blocks'])) {
            return FieldRenderer::legacyBlocksToFieldData(self::decode($page['content_blocks']));
        }

        return [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function decode(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (!is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
