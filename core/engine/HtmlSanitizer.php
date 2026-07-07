<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class HtmlSanitizer
{
    /** @var array<string, list<string>> */
    private const ALLOWED_TAGS = [
        'p' => ['class'],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        's' => [],
        'ul' => ['class'],
        'ol' => ['class', 'type', 'start'],
        'li' => ['class'],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'class'],
        'h1' => ['class'],
        'h2' => ['class'],
        'h3' => ['class'],
        'h4' => ['class'],
        'h5' => ['class'],
        'h6' => ['class'],
        'blockquote' => ['class'],
        'table' => ['class', 'border', 'cellpadding', 'cellspacing'],
        'thead' => ['class'],
        'tbody' => ['class'],
        'tr' => ['class'],
        'th' => ['class', 'colspan', 'rowspan', 'scope'],
        'td' => ['class', 'colspan', 'rowspan'],
        'span' => ['class'],
        'div' => ['class'],
        'hr' => ['class'],
        'pre' => ['class'],
        'code' => ['class'],
        'sub' => [],
        'sup' => [],
    ];

    public static function sanitize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $wrapped = '<?xml encoding="UTF-8"><div id="sanitize-root">' . $html . '</div>';
        $document->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('sanitize-root');
        if (!$root instanceof \DOMElement) {
            return '';
        }

        self::sanitizeNode($root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    private static function sanitizeNode(\DOMNode $node): void
    {
        if ($node instanceof \DOMElement) {
            $tag = strtolower($node->tagName);
            if (!isset(self::ALLOWED_TAGS[$tag])) {
                self::removeNode($node);

                return;
            }

            self::sanitizeAttributes($node, self::ALLOWED_TAGS[$tag]);
            self::sanitizeLinkRelations($node);
        }

        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            self::sanitizeNode($child);
        }
    }

    /**
     * @param list<string> $allowedAttributes
     */
    private static function sanitizeAttributes(\DOMElement $element, array $allowedAttributes): void
    {
        $toRemove = [];
        foreach ($element->attributes ?? [] as $attribute) {
            $name = strtolower($attribute->name);
            if (str_starts_with($name, 'on') || !in_array($name, $allowedAttributes, true)) {
                $toRemove[] = $name;
                continue;
            }

            $value = trim($attribute->value);
            if ($name === 'href' || $name === 'src') {
                if (!self::isSafeUrl($value)) {
                    $toRemove[] = $name;
                }
            }

            if ($name === 'style' && self::containsUnsafeStyle($value)) {
                $toRemove[] = $name;
            }
        }

        foreach ($toRemove as $name) {
            $element->removeAttribute($name);
        }
    }

    private static function sanitizeLinkRelations(\DOMElement $element): void
    {
        if (strtolower($element->tagName) !== 'a') {
            return;
        }

        if (strtolower($element->getAttribute('target')) === '_blank') {
            $rel = strtolower($element->getAttribute('rel'));
            $parts = array_filter(preg_split('/\s+/', $rel) ?: []);
            foreach (['noopener', 'noreferrer'] as $required) {
                if (!in_array($required, $parts, true)) {
                    $parts[] = $required;
                }
            }
            $element->setAttribute('rel', implode(' ', $parts));
        }
    }

    private static function removeNode(\DOMElement $element): void
    {
        $parent = $element->parentNode;
        if ($parent instanceof \DOMNode) {
            $parent->removeChild($element);
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        if ($url === '' || str_starts_with($url, '#')) {
            return true;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, './') || str_starts_with($url, '../')) {
            return !str_contains(strtolower($url), 'javascript:');
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return false;
        }

        if (!isset($parts['scheme'])) {
            return true;
        }

        $scheme = strtolower((string) $parts['scheme']);

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }

    private static function containsUnsafeStyle(string $style): bool
    {
        $lower = strtolower($style);

        return str_contains($lower, 'expression(')
            || str_contains($lower, 'javascript:')
            || str_contains($lower, 'behavior:')
            || str_contains($lower, '@import');
    }
}
