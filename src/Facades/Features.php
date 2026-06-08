<?php

namespace Webrek\FeatureFlags\Facades;

use Illuminate\Support\Facades\Facade;
use Webrek\FeatureFlags\FeatureManager;

/**
 * @method static bool active(string $feature, mixed $scope = null)
 * @method static bool inactive(string $feature, mixed $scope = null)
 * @method static string|null variant(string $feature, mixed $scope = null)
 * @method static \Webrek\FeatureFlags\PendingScopedFeature for(mixed $scope)
 * @method static void create(string $name, bool $active = true, ?int $rollout = null, list<array{attribute: string, operator?: string, value?: mixed}> $constraints = [], list<array{name: string, weight: int}> $variants = [])
 * @method static void activate(string $name)
 * @method static void deactivate(string $name)
 * @method static void rollout(string $name, ?int $percentage)
 * @method static void forget(string $name)
 * @method static array<string, \Webrek\FeatureFlags\FeatureDefinition> all()
 * @method static void flushCache()
 *
 * @see FeatureManager
 */
class Features extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FeatureManager::class;
    }
}
