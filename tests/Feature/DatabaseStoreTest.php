<?php

namespace Webrek\FeatureFlags\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Webrek\FeatureFlags\FeatureManager;
use Webrek\FeatureFlags\Tests\TestCase;

class DatabaseStoreTest extends TestCase
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

    public function test_it_creates_and_resolves_a_feature(): void
    {
        $this->manager()->create('new-checkout', rollout: 100);

        $this->assertTrue($this->manager()->active('new-checkout', 'user-1'));
    }

    public function test_it_activates_and_deactivates(): void
    {
        $this->manager()->create('beta');
        $this->assertTrue($this->manager()->active('beta', 'user-1'));

        $this->manager()->deactivate('beta');
        $this->assertFalse($this->manager()->active('beta', 'user-1'));

        $this->manager()->activate('beta');
        $this->assertTrue($this->manager()->active('beta', 'user-1'));
    }

    public function test_it_updates_rollout(): void
    {
        $this->manager()->create('gradual', rollout: 0);
        $this->assertFalse($this->manager()->active('gradual', 'user-1'));

        $this->manager()->rollout('gradual', 100);
        $this->assertTrue($this->manager()->active('gradual', 'user-1'));
    }

    public function test_it_clamps_rollout_to_a_valid_range(): void
    {
        $this->manager()->create('clamped');
        $this->manager()->rollout('clamped', 250);

        $this->assertSame(100, $this->manager()->all()['clamped']->rollout);
    }

    public function test_it_lists_and_forgets(): void
    {
        $this->manager()->create('a');
        $this->manager()->create('b');

        $this->assertEqualsCanonicalizing(['a', 'b'], array_keys($this->manager()->all()));

        $this->manager()->forget('a');

        $this->assertEqualsCanonicalizing(['b'], array_keys($this->manager()->all()));
    }
}
