<?php

namespace Webrek\FeatureFlags\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webrek\FeatureFlags\Support\Bucket;

class BucketTest extends TestCase
{
    public function test_percentage_is_in_range_and_stable(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $value = Bucket::percentage('feature', "user-{$i}");
            $this->assertGreaterThanOrEqual(0, $value);
            $this->assertLessThan(100, $value);
        }

        $this->assertSame(
            Bucket::percentage('feature', 'user-1'),
            Bucket::percentage('feature', 'user-1'),
        );
    }

    public function test_percentage_varies_across_scopes(): void
    {
        $values = array_map(fn (int $i): int => Bucket::percentage('feature', "user-{$i}"), range(1, 200));

        $this->assertGreaterThan(1, count(array_unique($values)));
    }

    public function test_variant_is_stable_and_weighted(): void
    {
        $variants = [
            ['name' => 'blue', 'weight' => 50],
            ['name' => 'green', 'weight' => 50],
        ];

        $first = Bucket::variant('exp', 'user-1', $variants);

        $this->assertContains($first, ['blue', 'green']);
        $this->assertSame($first, Bucket::variant('exp', 'user-1', $variants));
    }

    public function test_variant_respects_zero_weight(): void
    {
        $variants = [
            ['name' => 'only', 'weight' => 1],
            ['name' => 'never', 'weight' => 0],
        ];

        foreach (range(1, 50) as $i) {
            $this->assertSame('only', Bucket::variant('exp', "user-{$i}", $variants));
        }
    }

    public function test_variant_returns_null_without_positive_weight(): void
    {
        $this->assertNull(Bucket::variant('exp', 'user-1', [['name' => 'x', 'weight' => 0]]));
        $this->assertNull(Bucket::variant('exp', 'user-1', []));
    }
}
