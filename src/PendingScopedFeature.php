<?php

namespace Webrek\FeatureFlags;

/**
 * Fluent helper returned by {@see FeatureManager::for()} so a scope can be set
 * once and reused: feature()->for($user)->active('new-checkout').
 */
class PendingScopedFeature
{
    public function __construct(
        private readonly FeatureManager $manager,
        private readonly mixed $scope,
    ) {}

    public function active(string $feature): bool
    {
        return $this->manager->active($feature, $this->scope);
    }

    public function inactive(string $feature): bool
    {
        return $this->manager->inactive($feature, $this->scope);
    }

    public function variant(string $feature): ?string
    {
        return $this->manager->variant($feature, $this->scope);
    }
}
