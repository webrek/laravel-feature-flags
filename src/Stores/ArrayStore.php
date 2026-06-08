<?php

namespace Webrek\FeatureFlags\Stores;

use Webrek\FeatureFlags\Contracts\Store;
use Webrek\FeatureFlags\FeatureDefinition;

/**
 * An in-memory store, seeded from config. Ideal for tests and for apps that
 * prefer declaring flags in code rather than a database.
 */
class ArrayStore implements Store
{
    /** @var array<string, FeatureDefinition> */
    private array $features = [];

    /**
     * @param  array<string, FeatureDefinition>  $features
     */
    public function __construct(array $features = [])
    {
        $this->features = $features;
    }

    /**
     * @param  array<string, array<string, mixed>>  $config
     */
    public static function fromConfig(array $config): self
    {
        $features = [];

        foreach ($config as $name => $attributes) {
            $features[$name] = FeatureDefinition::fromArray($name, $attributes);
        }

        return new self($features);
    }

    public function get(string $name): ?FeatureDefinition
    {
        return $this->features[$name] ?? null;
    }

    public function all(): array
    {
        return $this->features;
    }

    public function put(FeatureDefinition $definition): void
    {
        $this->features[$definition->name] = $definition;
    }

    public function forget(string $name): void
    {
        unset($this->features[$name]);
    }
}
