<?php

namespace Webrek\FeatureFlags\Contracts;

use Webrek\FeatureFlags\FeatureDefinition;

interface Store
{
    public function get(string $name): ?FeatureDefinition;

    /**
     * @return array<string, FeatureDefinition>
     */
    public function all(): array;

    public function put(FeatureDefinition $definition): void;

    public function forget(string $name): void;
}
