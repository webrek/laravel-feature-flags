<?php

namespace Webrek\FeatureFlags;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Webrek\FeatureFlags\Console\ActivateFeatureCommand;
use Webrek\FeatureFlags\Console\DeactivateFeatureCommand;
use Webrek\FeatureFlags\Console\ListFeaturesCommand;
use Webrek\FeatureFlags\Console\RolloutFeatureCommand;
use Webrek\FeatureFlags\Contracts\Store;
use Webrek\FeatureFlags\Http\Middleware\EnsureFeatureIsActive;
use Webrek\FeatureFlags\Stores\ArrayStore;
use Webrek\FeatureFlags\Stores\DatabaseStore;

class FeatureFlagsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/feature-flags.php', 'feature-flags');

        $this->app->singleton(Store::class, fn ($app): Store => $this->makeStore($app['config']->get('feature-flags', [])));

        $this->app->singleton(FeatureManager::class, fn ($app): FeatureManager => new FeatureManager($app->make(Store::class)));
    }

    public function boot(): void
    {
        Blade::if('feature', fn (string $feature, mixed $scope = null): bool => $this->app->make(FeatureManager::class)->active($feature, $scope));

        $this->app->make(Router::class)->aliasMiddleware('feature', EnsureFeatureIsActive::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/feature-flags.php' => $this->app->configPath('feature-flags.php'),
            ], 'feature-flags-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/create_features_table.php.stub' => $this->app->databasePath('migrations/' . date('Y_m_d_His') . '_create_features_table.php'),
            ], 'feature-flags-migrations');

            $this->commands([
                ListFeaturesCommand::class,
                ActivateFeatureCommand::class,
                DeactivateFeatureCommand::class,
                RolloutFeatureCommand::class,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function makeStore(array $config): Store
    {
        $name = $config['default'] ?? 'database';
        $driver = $config['stores'][$name]['driver'] ?? $name;

        return match ($driver) {
            'array' => ArrayStore::fromConfig($config['features'] ?? []),
            'database' => new DatabaseStore($config['stores'][$name]['model'] ?? Models\Feature::class),
            default => throw new InvalidArgumentException("Unsupported feature flag store [{$driver}]."),
        };
    }
}
