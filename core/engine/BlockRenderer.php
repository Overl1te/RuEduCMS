<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use RuEdu\Model\Article;
use RuEdu\Model\Setting;

class BlockRenderer
{
    public const HOME_LAYOUT_KEY = 'home_layout';

    /**
     * @param array<string, mixed> $context
     */
    public static function renderHome(array $context = []): string
    {
        $blocks = self::getHomeLayout();
        if ($blocks === []) {
            return '';
        }

        $context['articles'] = $context['articles'] ?? Article::getAll('published', 10);

        return self::render($blocks, $context);
    }

    /**
     * @return list<array{type: string, props?: array<string, mixed>}>
     */
    public static function getHomeLayout(): array
    {
        $raw = Setting::get(self::HOME_LAYOUT_KEY, '');
        if (!is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? self::normalizeBlocks($decoded) : [];
    }

    public static function saveHomeLayout(array $blocks): void
    {
        Setting::set(self::HOME_LAYOUT_KEY, json_encode(self::normalizeBlocks($blocks), JSON_UNESCAPED_UNICODE), 'theme');
        Cache::flush();
    }

    /**
     * @param list<array<string, mixed>>|string|null $blocks
     * @param array<string, mixed> $context
     */
    public static function render(array|string|null $blocks, array $context = []): string
    {
        if (is_string($blocks)) {
            $decoded = json_decode($blocks, true);
            $blocks = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($blocks) || $blocks === []) {
            return '';
        }

        $blocks = self::normalizeBlocks($blocks);
        $theme = (string) Config::get('theme', 'default-school');
        $themePath = THEMES_PATH . '/' . $theme;
        $html = '';

        foreach ($blocks as $block) {
            $type = $block['type'];
            $props = BlockRegistry::mergeProps($type, $block['props'] ?? []);
            $file = $themePath . '/templates/blocks/' . $type . '.php';

            if (!is_file($file)) {
                continue;
            }

            $site_name = $context['site_name'] ?? Config::get('site_name', '');
            $articles = $context['articles'] ?? [];

            ob_start();
            include $file;
            $html .= (string) ob_get_clean();
        }

        return $html;
    }

    /**
     * @param list<array<string, mixed>> $blocks
     * @return list<array{type: string, props: array<string, mixed>}>
     */
    public static function normalizeBlocks(array $blocks): array
    {
        $normalized = [];

        foreach ($blocks as $block) {
            if (!is_array($block) || empty($block['type'])) {
                continue;
            }

            $type = (string) $block['type'];
            if (BlockRegistry::get($type) === null) {
                continue;
            }

            $props = $block['props'] ?? [];
            if (!is_array($props)) {
                $props = [];
            }

            $normalized[] = [
                'type' => $type,
                'props' => BlockRegistry::mergeProps($type, $props),
            ];
        }

        return $normalized;
    }

    /**
     * @return list<array{type: string, props: array<string, mixed>}>
     */
    public static function defaultHomeLayout(): array
    {
        return self::normalizeBlocks([
            ['type' => 'hero'],
            ['type' => 'stats'],
            ['type' => 'quick_links'],
            ['type' => 'latest_news'],
            ['type' => 'cta'],
        ]);
    }
}
