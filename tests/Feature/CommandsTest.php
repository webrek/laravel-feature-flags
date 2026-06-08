<?php

namespace Webrek\FeatureFlags\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Webrek\FeatureFlags\FeatureManager;
use Webrek\FeatureFlags\Tests\TestCase;

class CommandsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('features', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('active')->default(true);
            $table->unsignedTinyInteger('rollout')->nullable();
            $table->json('constraints')->nullable();
            $table->json('variants')->nullable();
            $table->timestamps();
        });
    }

    private function manager(): FeatureManager
    {
        return $this->app->make(FeatureManager::class);
    }

    public function test_list_shows_features(): void
    {
        $this->manager()->create('new-checkout', rollout: 25);
        $this->manager()->create('button-color', variants: [['name' => 'blue', 'weight' => 1]]);

        $this->artisan('feature:list')
            ->expectsOutputToContain('new-checkout')
            ->expectsOutputToContain('blue')
            ->assertSuccessful();
    }

    public function test_list_reports_when_empty(): void
    {
        $this->artisan('feature:list')
            ->expectsOutputToContain('No features')
            ->assertSuccessful();
    }

    public function test_activate(): void
    {
        $this->manager()->create('x', active: false);

        $this->artisan('feature:activate', ['name' => 'x'])
            ->expectsOutputToContain('x')
            ->assertSuccessful();

        $this->assertTrue($this->manager()->all()['x']->active);
    }

    public function test_deactivate(): void
    {
        $this->manager()->create('x');

        $this->artisan('feature:deactivate', ['name' => 'x'])->assertSuccessful();

        $this->assertFalse($this->manager()->all()['x']->active);
    }

    public function test_rollout(): void
    {
        $this->manager()->create('x');

        $this->artisan('feature:rollout', ['name' => 'x', 'percentage' => '50'])
            ->expectsOutputToContain('50')
            ->assertSuccessful();

        $this->assertSame(50, $this->manager()->all()['x']->rollout);
    }
}
