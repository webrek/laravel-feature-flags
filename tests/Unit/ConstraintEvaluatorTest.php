<?php

namespace Webrek\FeatureFlags\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webrek\FeatureFlags\Support\ConstraintEvaluator;

class ConstraintEvaluatorTest extends TestCase
{
    public function test_equality(): void
    {
        $this->assertTrue(ConstraintEvaluator::passes(
            [['attribute' => 'plan', 'operator' => '=', 'value' => 'pro']],
            ['plan' => 'pro'],
        ));

        $this->assertFalse(ConstraintEvaluator::passes(
            [['attribute' => 'plan', 'operator' => '=', 'value' => 'pro']],
            ['plan' => 'free'],
        ));
    }

    public function test_in_and_not_in(): void
    {
        $this->assertTrue(ConstraintEvaluator::passes(
            [['attribute' => 'plan', 'operator' => 'in', 'value' => ['pro', 'enterprise']]],
            ['plan' => 'enterprise'],
        ));

        $this->assertTrue(ConstraintEvaluator::passes(
            [['attribute' => 'plan', 'operator' => 'not_in', 'value' => ['pro']]],
            ['plan' => 'free'],
        ));
    }

    public function test_numeric_comparisons(): void
    {
        $this->assertTrue(ConstraintEvaluator::passes(
            [['attribute' => 'age', 'operator' => '>=', 'value' => 18]],
            ['age' => 21],
        ));

        $this->assertFalse(ConstraintEvaluator::passes(
            [['attribute' => 'age', 'operator' => '>=', 'value' => 18]],
            ['age' => 16],
        ));
    }

    public function test_all_constraints_must_pass(): void
    {
        $constraints = [
            ['attribute' => 'plan', 'operator' => '=', 'value' => 'pro'],
            ['attribute' => 'country', 'operator' => 'in', 'value' => ['MX', 'US']],
        ];

        $this->assertTrue(ConstraintEvaluator::passes($constraints, ['plan' => 'pro', 'country' => 'MX']));
        $this->assertFalse(ConstraintEvaluator::passes($constraints, ['plan' => 'pro', 'country' => 'CA']));
    }
}
