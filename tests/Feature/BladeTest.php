<?php

namespace Webrek\FeatureFlags\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Webrek\FeatureFlags\Tests\TestCase;

class BladeTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('feature-flags.default', 'array');
        $app['config']->set('feature-flags.features', [
            'shiny' => ['active' => true],
            'dark' => ['active' => false],
        ]);
    }

    public function test_the_feature_directive_renders_conditionally(): void
    {
        $this->assertSame('YES', trim(Blade::render("@feature('shiny')\nYES\n@endfeature")));
        $this->assertSame('', trim(Blade::render("@feature('dark')\nYES\n@endfeature")));
    }

    public function test_the_unless_directive_is_available(): void
    {
        $this->assertSame('HIDDEN', trim(Blade::render("@unlessfeature('dark')\nHIDDEN\n@endfeature")));
    }
}
