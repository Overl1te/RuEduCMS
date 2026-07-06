<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class FieldLocation
{
    /**
     * @return list<array{param: string, operator: string, value: string}>
     */
    public static function parseRules(mixed $locations): array
    {
        if (is_string($locations)) {
            $locations = json_decode($locations, true);
        }
        if (!is_array($locations)) {
            return [];
        }

        $rules = [];
        foreach ($locations as $rule) {
            if (!is_array($rule) || empty($rule['param'])) {
                continue;
            }
            $rules[] = [
                'param' => (string) $rule['param'],
                'operator' => (string) ($rule['operator'] ?? '=='),
                'value' => (string) ($rule['value'] ?? ''),
            ];
        }

        return $rules;
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function matches(array $rules, array $context): bool
    {
        if ($rules === []) {
            return false;
        }

        foreach ($rules as $rule) {
            if (!self::matchRule($rule, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $context
     */
    private static function matchRule(array $rule, array $context): bool
    {
        $param = $rule['param'];
        $expected = $rule['value'];
        $op = $rule['operator'];

        $actual = match ($param) {
            'page_type' => (string) ($context['page_type'] ?? ''),
            'page_slug' => (string) ($context['page_slug'] ?? ''),
            'system_page' => (string) ($context['system_page'] ?? ''),
            'all_pages' => '1',
            default => (string) ($context[$param] ?? ''),
        };

        return match ($op) {
            '!=' => $actual !== $expected,
            default => $actual === $expected,
        };
    }

    /**
     * @return list<string>
     */
    public static function locationParams(): array
    {
        return ['page_type', 'page_slug', 'system_page', 'all_pages'];
    }
}
