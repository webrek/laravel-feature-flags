<?php

namespace Webrek\FeatureFlags\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webrek\FeatureFlags\FeatureDefinition;
use Webrek\FeatureFlags\FeatureManager;
use Webrek\FeatureFlags\Stores\ArrayStore;
use Webrek\FeatureFlags\Tests\Support\ArrayScope;

class ResolutionTest extends TestCase
{
    private function manager(): FeatureManager
    {
        return new FeatureManager(new ArrayStore([
            'on' => new FeatureDefinition('on', true),
            'off' => new FeatureDefinition('off', false),
            'none' => new FeatureDefinition('none', true, 0),
            'all' => new FeatureDefinition('all', true, 100),
            'half' => new FeatureDefinition('half', true, 50),
            'pro' => new FeatureDefinition('pro', true, null, [
                ['attribute' => 'plan', 'operator' => 'in', 'value' => ['pro', 'enterprise']],
            ]),
            'ab' => new FeatureDefinition('ab', true, null, [], [
                ['name' => 'blue', 'weight' => 50],
                ['name' => 'green', 'weight' => 50],
            ]),
        ]));
    }

    public function test_simple_on_off(): void
    {
        $manager = $this->manager();

        $this->assertTrue($manager->active('on', 'user-1'));
        $this->assertFalse($manager->active('off', 'user-1'));
        $this->assertFalse($manager->active('undefined', 'user-1'));
    }

    public function test_rollout_extremes(): void
    {
        $manager = $this->manager();

        foreach (range(1, 50) as $i) {
            $this->assertFalse($manager->active('none', "user-{$i}"));
            $this->assertTrue($manager->active('all', "user-{$i}"));
        }
    }

    public function test_partial_rollout_is_deterministic_and_splits(): void
    {
        $manager = $this->manager();

        $this->assertSame($manager->active('half', 'user-1'), $manager->active('half', 'user-1'));

        $active = array_filter(range(1, 200), fn (int $i): bool => $manager->active('half', "user-{$i}"));

        $this->assertGreaterThan(0, count($active));
        $this->assertLessThan(200, count($active));
    }

    public function test_constraints_target_by_attribute(): void
    {
        $manager = $this->manager();

        $this->assertTrue($manager->active('pro', new ArrayScope('a', ['plan' => 'enterprise'])));
        $this->assertFalse($manager->active('pro', new ArrayScope('b', ['plan' => 'free'])));
    }

    public function test_variants_are_assigned_and_make_the_feature_active(): void
    {
        $manager = $this->manager();

        $variant = $manager->variant('ab', 'user-1');

        $this->assertContains($variant, ['blue', 'green']);
        $this->assertTrue($manager->active('ab', 'user-1'));
        $this->assertSame($variant, $manager->variant('ab', 'user-1'));
    }

    public function test_for_scope_is_fluent(): void
    {
        $manager = $this->manager();

        $this->assertTrue($manager->for('user-1')->active('on'));
        $this->assertFalse($manager->for('user-1')->inactive('on'));
    }
}
