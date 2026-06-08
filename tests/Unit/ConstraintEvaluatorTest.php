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

    public function test_every_operator(): void
    {
        $pass = fn (string $op, mixed $value, mixed $actual): bool => ConstraintEvaluator::passes(
            [['attribute' => 'x', 'operator' => $op, 'value' => $value]],
            ['x' => $actual],
        );

        $this->assertTrue($pass('=', 'a', 'a'));
        $this->assertFalse($pass('=', 'a', 'b'));

        $this->assertTrue($pass('!=', 'a', 'b'));
        $this->assertFalse($pass('!=', 'a', 'a'));

        $this->assertTrue($pass('in', ['a', 'b'], 'b'));
        $this->assertFalse($pass('in', ['a', 'b'], 'c'));

        $this->assertTrue($pass('not_in', ['a'], 'b'));
        $this->assertFalse($pass('not_in', ['a'], 'a'));

        $this->assertTrue($pass('>', 5, 6));
        $this->assertFalse($pass('>', 5, 5));

        $this->assertTrue($pass('>=', 5, 5));
        $this->assertFalse($pass('>=', 5, 4));

        $this->assertTrue($pass('<', 5, 4));
        $this->assertFalse($pass('<', 5, 5));

        $this->assertTrue($pass('<=', 5, 5));
        $this->assertFalse($pass('<=', 5, 6));

        $this->assertTrue($pass('contains', 'ell', 'hello'));
        $this->assertFalse($pass('contains', 'xyz', 'hello'));

        // Unknown operators never match.
        $this->assertFalse($pass('~=', 'a', 'a'));
        // Non-numeric comparisons never match.
        $this->assertFalse($pass('>', 5, 'abc'));
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
