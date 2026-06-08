<?php

namespace Webrek\FeatureFlags\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Webrek\FeatureFlags\Facades\Features;
use Webrek\FeatureFlags\Tests\Support\User;
use Webrek\FeatureFlags\Tests\TestCase;

class DefaultScopeTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('feature-flags.default', 'array');
        $app['config']->set('feature-flags.features', [
            'pro-only' => [
                'active' => true,
                'constraints' => [
                    ['attribute' => 'plan', 'operator' => '=', 'value' => 'pro'],
                ],
            ],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('plan');
        });
    }

    public function test_it_uses_the_authenticated_user_as_the_default_scope(): void
    {
        $pro = User::create(['plan' => 'pro']);
        $free = User::create(['plan' => 'free']);

        $this->actingAs($pro);
        $this->assertTrue(Features::active('pro-only'));
        $this->assertTrue(feature('pro-only'));

        $this->actingAs($free);
        $this->assertFalse(Features::active('pro-only'));
    }
}
