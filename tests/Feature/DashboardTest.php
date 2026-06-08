<?php

namespace Webrek\FeatureFlags\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Webrek\FeatureFlags\FeatureManager;
use Webrek\FeatureFlags\Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_index_lists_features(): void
    {
        $this->manager()->create('new-checkout', rollout: 25);

        $this->get('/feature-flags')
            ->assertOk()
            ->assertSee('new-checkout')
            ->assertSee('Feature Flags');
    }

    public function test_it_creates_a_feature(): void
    {
        $this->post('/feature-flags', ['name' => 'beta', 'rollout' => 50])
            ->assertRedirect();

        $this->assertArrayHasKey('beta', $this->manager()->all());
        $this->assertSame(50, $this->manager()->all()['beta']->rollout);
    }

    public function test_it_toggles_a_feature(): void
    {
        $this->manager()->create('beta');
        $this->assertTrue($this->manager()->all()['beta']->active);

        $this->post('/feature-flags/beta/toggle')->assertRedirect();

        $this->assertFalse($this->manager()->all()['beta']->active);
    }

    public function test_it_updates_rollout(): void
    {
        $this->manager()->create('beta');

        $this->post('/feature-flags/beta/rollout', ['rollout' => 75])->assertRedirect();

        $this->assertSame(75, $this->manager()->all()['beta']->rollout);
    }

    public function test_it_deletes_a_feature(): void
    {
        $this->manager()->create('beta');

        $this->delete('/feature-flags/beta')->assertRedirect();

        $this->assertArrayNotHasKey('beta', $this->manager()->all());
    }
}
