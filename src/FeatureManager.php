<?php

namespace Webrek\FeatureFlags;

use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Throwable;
use Webrek\FeatureFlags\Contracts\FeatureScope;
use Webrek\FeatureFlags\Contracts\Store;
use Webrek\FeatureFlags\Support\Bucket;
use Webrek\FeatureFlags\Support\ConstraintEvaluator;

class FeatureManager
{
    private const GLOBAL_SCOPE = '__global__';

    /**
     * Per-request resolution cache, keyed by "feature\0scope".
     *
     * @var array<string, array{active: bool, variant: string|null}>
     */
    private array $resolved = [];

    public function __construct(private readonly Store $store) {}

    public function active(string $feature, mixed $scope = null): bool
    {
        return $this->resolve($feature, $scope)['active'];
    }

    public function inactive(string $feature, mixed $scope = null): bool
    {
        return ! $this->active($feature, $scope);
    }

    public function variant(string $feature, mixed $scope = null): ?string
    {
        return $this->resolve($feature, $scope)['variant'];
    }

    public function for(mixed $scope): PendingScopedFeature
    {
        return new PendingScopedFeature($this, $scope);
    }

    /**
     * @param  list<array{attribute: string, operator?: string, value?: mixed}>  $constraints
     * @param  list<array{name: string, weight: int}>  $variants
     */
    public function create(
        string $name,
        bool $active = true,
        ?int $rollout = null,
        array $constraints = [],
        array $variants = [],
    ): void {
        $this->store->put(new FeatureDefinition($name, $active, $rollout, $constraints, $variants));
        $this->resolved = [];
    }

    public function activate(string $name): void
    {
        $this->mutate($name, fn (FeatureDefinition $d): FeatureDefinition => new FeatureDefinition($name, true, $d->rollout, $d->constraints, $d->variants));
    }

    public function deactivate(string $name): void
    {
        $this->mutate($name, fn (FeatureDefinition $d): FeatureDefinition => new FeatureDefinition($name, false, $d->rollout, $d->constraints, $d->variants));
    }

    public function rollout(string $name, ?int $percentage): void
    {
        $percentage = $percentage === null ? null : max(0, min(100, $percentage));

        $this->mutate($name, fn (FeatureDefinition $d): FeatureDefinition => new FeatureDefinition($name, $d->active, $percentage, $d->constraints, $d->variants));
    }

    public function forget(string $name): void
    {
        $this->store->forget($name);
        $this->resolved = [];
    }

    /**
     * @return array<string, FeatureDefinition>
     */
    public function all(): array
    {
        return $this->store->all();
    }

    public function flushCache(): void
    {
        $this->resolved = [];
    }

    /**
     * @param  callable(FeatureDefinition): FeatureDefinition  $callback
     */
    private function mutate(string $name, callable $callback): void
    {
        $current = $this->store->get($name) ?? new FeatureDefinition($name);
        $this->store->put($callback($current));
        $this->resolved = [];
    }

    /**
     * @return array{active: bool, variant: string|null}
     */
    private function resolve(string $feature, mixed $scope): array
    {
        [$identifier, $attributes] = $this->resolveScope($scope);

        return $this->resolved[$feature . "\0" . $identifier] ??= $this->compute($feature, $identifier, $attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{active: bool, variant: string|null}
     */
    private function compute(string $feature, string $identifier, array $attributes): array
    {
        $inactive = ['active' => false, 'variant' => null];

        $definition = $this->store->get($feature);

        if ($definition === null || ! $definition->active) {
            return $inactive;
        }

        if ($definition->constraints !== [] && ! ConstraintEvaluator::passes($definition->constraints, $attributes)) {
            return $inactive;
        }

        if ($definition->rollout !== null && Bucket::percentage($feature, $identifier) >= $definition->rollout) {
            return $inactive;
        }

        if ($definition->variants !== []) {
            $variant = Bucket::variant($feature, $identifier, $definition->variants);

            return ['active' => $variant !== null, 'variant' => $variant];
        }

        return ['active' => true, 'variant' => null];
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function resolveScope(mixed $scope): array
    {
        $scope ??= $this->defaultScope();

        if ($scope === null) {
            return [self::GLOBAL_SCOPE, []];
        }

        if ($scope instanceof FeatureScope) {
            return [$scope->featureScopeIdentifier(), $scope->featureScopeAttributes()];
        }

        if ($scope instanceof Model) {
            return [$scope->getMorphClass() . ':' . $scope->getKey(), $scope->attributesToArray()];
        }

        if ($scope instanceof Authenticatable) {
            return [(string) $scope->getAuthIdentifier(), []];
        }

        return [(string) $scope, []];
    }

    private function defaultScope(): mixed
    {
        try {
            $container = Container::getInstance();

            if (! $container->bound('auth')) {
                return null;
            }

            return $container->make('auth')->guard()->user();
        } catch (Throwable) {
            return null;
        }
    }
}
