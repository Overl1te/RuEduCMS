<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class ElementStyles
{
    /** @var list<string> */
    private const ALLOWED = [
        'color', 'background', 'backgroundColor', 'fontSize', 'fontWeight', 'textAlign',
        'paddingTop', 'paddingBottom', 'paddingLeft', 'paddingRight',
        'marginTop', 'marginBottom', 'borderRadius', 'maxWidth',
    ];

    /**
     * @param array<string, mixed> $style
     * @param array<string, array<string, mixed>> $elementStyles
     */
    public static function wrap(string $blockId, string $html, array $style, array $elementStyles): string
    {
        $scoped = self::renderScopedCss($blockId, $style, $elementStyles);
        $class = 'fg-block fg-block--' . preg_replace('/[^a-z0-9_-]/i', '', $blockId);

        return $scoped . '<div class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" data-fg-block="' . htmlspecialchars($blockId, ENT_QUOTES, 'UTF-8') . '">' . $html . '</div>';
    }

    /**
     * @param array<string, mixed> $style
     * @param array<string, array<string, mixed>> $elementStyles
     */
    public static function attrs(array $style, array $elementStyles, string $layout): string
    {
        $inline = self::inlineStyle($style);
        if ($inline === '') {
            return '';
        }

        return 'style="' . htmlspecialchars($inline, ENT_QUOTES, 'UTF-8') . '"';
    }

    /**
     * @param array<string, mixed> $style
     * @param array<string, array<string, mixed>> $elementStyles
     */
    public static function renderScopedCss(string $blockId, array $style, array $elementStyles): string
    {
        $safeId = preg_replace('/[^a-z0-9_-]/i', '', $blockId) ?: 'block';
        $rules = [];

        $blockCss = self::inlineStyle($style);
        if ($blockCss !== '') {
            $rules[] = '.fg-block--' . $safeId . '{' . $blockCss . '}';
        }

        foreach ($elementStyles as $element => $props) {
            if (!is_array($props)) {
                continue;
            }
            $css = self::inlineStyle($props);
            if ($css === '') {
                continue;
            }
            $rules[] = '.fg-block--' . $safeId . ' [data-fg-element="' . self::escapeAttr((string) $element) . '"]{' . $css . '}';
        }

        if ($rules === []) {
            return '';
        }

        return '<style>' . implode('', $rules) . '</style>';
    }

    /**
     * @param array<string, mixed> $style
     */
    public static function inlineStyle(array $style): string
    {
        $parts = [];
        foreach ($style as $key => $value) {
            if (!in_array((string) $key, self::ALLOWED, true)) {
                continue;
            }
            $cssKey = preg_replace('/([A-Z])/', '-$1', (string) $key);
            $cssKey = strtolower((string) $cssKey);
            $val = trim((string) $value);
            if ($val === '') {
                continue;
            }
            if (!self::isSafeValue($val)) {
                continue;
            }
            $parts[] = $cssKey . ':' . $val;
        }

        return implode(';', $parts);
    }

    /**
     * @return list<string>
     */
    public static function styleKeys(): array
    {
        return self::ALLOWED;
    }

    private static function isSafeValue(string $value): bool
    {
        if (str_contains($value, ';') || str_contains($value, '{') || str_contains($value, '}')) {
            return false;
        }

        return (bool) preg_match('/^[#a-zA-Z0-9(),.%\s\-+\'"\/]+$/', $value);
    }

    private static function escapeAttr(string $value): string
    {
        return preg_replace('/[^a-z0-9_-]/i', '', $value) ?: 'el';
    }
}
