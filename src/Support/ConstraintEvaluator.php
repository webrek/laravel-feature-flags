<?php

namespace Webrek\FeatureFlags\Support;

use Illuminate\Support\Arr;

/**
 * Evaluates targeting constraints against a scope's attributes. All constraints
 * must pass (logical AND) for the scope to match.
 */
final class ConstraintEvaluator
{
    /**
     * @param  list<array{attribute: string, operator?: string, value?: mixed}>  $constraints
     * @param  array<string, mixed>  $attributes
     */
    public static function passes(array $constraints, array $attributes): bool
    {
        foreach ($constraints as $constraint) {
            if (! self::matches($constraint, $attributes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array{attribute: string, operator?: string, value?: mixed}  $constraint
     * @param  array<string, mixed>  $attributes
     */
    private static function matches(array $constraint, array $attributes): bool
    {
        $actual = Arr::get($attributes, $constraint['attribute']);
        $operator = $constraint['operator'] ?? '=';
        $expected = $constraint['value'] ?? null;

        return match ($operator) {
            '=', '==', 'equals' => $actual == $expected,
            '!=', 'not' => $actual != $expected,
            'in' => is_array($expected) && in_array($actual, $expected),
            'not_in' => is_array($expected) && ! in_array($actual, $expected),
            '>' => is_numeric($actual) && is_numeric($expected) && $actual > $expected,
            '>=' => is_numeric($actual) && is_numeric($expected) && $actual >= $expected,
            '<' => is_numeric($actual) && is_numeric($expected) && $actual < $expected,
            '<=' => is_numeric($actual) && is_numeric($expected) && $actual <= $expected,
            'contains' => is_string($actual) && is_string($expected) && str_contains($actual, $expected),
            default => false,
        };
    }
}
