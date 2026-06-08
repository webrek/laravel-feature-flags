<?php

namespace Webrek\FeatureFlags\Tests\Feature;

use Illuminate\Routing\Router;
use Webrek\FeatureFlags\Tests\TestCase;

class MiddlewareTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('feature-flags.default', 'array');
        $app['config']->set('feature-flags.features', [
            'beta' => ['active' => true],
            'hidden' => ['active' => false],
        ]);
    }

    protected function defineRoutes($router): void
    {
        /** @var Router $router */
        $router->middleware('feature:beta')->get('/beta', fn (): string => 'ok');
        $router->middleware('feature:hidden')->get('/hidden', fn (): string => 'ok');
    }

    public function test_it_allows_an_active_feature(): void
    {
        $this->get('/beta')->assertOk()->assertSee('ok');
    }

    public function test_it_blocks_an_inactive_feature(): void
    {
        $this->get('/hidden')->assertNotFound();
    }
}
