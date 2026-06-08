<?php

namespace Webrek\FeatureFlags\Support;

/**
 * Deterministic bucketing. The same (feature, scope) pair always lands in the
 * same bucket, so a rollout is stable as you raise the percentage and a scope
 * keeps the same variant across requests.
 */
final class Bucket
{
    /**
     * A stable value in the range 0–99 for a (feature, scope) pair.
     */
    public static function percentage(string $feature, string $scope): int
    {
        return self::hash($feature . '|' . $scope) % 100;
    }

    /**
     * Pick a variant for a (feature, scope) pair, weighted by each variant's
     * weight. Returns null when there is no positive weight to assign.
     *
     * @param  list<array{name: string, weight: int}>  $variants
     */
    public static function variant(string $feature, string $scope, array $variants): ?string
    {
        $total = array_sum(array_map(fn (array $v): int => max(0, (int) $v['weight']), $variants));

        if ($total <= 0) {
            return null;
        }

        $point = self::hash($feature . '|variant|' . $scope) % $total;
        $cursor = 0;

        foreach ($variants as $variant) {
            $cursor += max(0, (int) $variant['weight']);

            if ($point < $cursor) {
                return $variant['name'];
            }
        }

        return null;
    }

    private static function hash(string $value): int
    {
        // crc32 is fast and stable across requests; abs guards 32-bit platforms.
        return abs(crc32($value));
    }
}
