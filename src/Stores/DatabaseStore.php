<?php

namespace Webrek\FeatureFlags\Stores;

use Webrek\FeatureFlags\Contracts\Store;
use Webrek\FeatureFlags\FeatureDefinition;
use Webrek\FeatureFlags\Models\Feature;

/**
 * Persists feature definitions in a database table, so they can be flipped at
 * runtime without a deploy.
 */
class DatabaseStore implements Store
{
    /**
     * @param  class-string<Feature>  $model
     */
    public function __construct(private string $model = Feature::class) {}

    public function get(string $name): ?FeatureDefinition
    {
        $row = $this->model::query()->where('name', $name)->first();

        return $row ? $this->toDefinition($row) : null;
    }

    public function all(): array
    {
        return $this->model::query()
            ->get()
            ->mapWithKeys(fn (Feature $row): array => [$row->name => $this->toDefinition($row)])
            ->all();
    }

    public function put(FeatureDefinition $definition): void
    {
        $this->model::query()->updateOrCreate(
            ['name' => $definition->name],
            [
                'active' => $definition->active,
                'rollout' => $definition->rollout,
                'constraints' => $definition->constraints === [] ? null : $definition->constraints,
                'variants' => $definition->variants === [] ? null : $definition->variants,
            ],
        );
    }

    public function forget(string $name): void
    {
        $this->model::query()->where('name', $name)->delete();
    }

    private function toDefinition(Feature $row): FeatureDefinition
    {
        return new FeatureDefinition(
            $row->name,
            $row->active,
            $row->rollout,
            $row->constraints ?? [],
            $row->variants ?? [],
        );
    }
}
