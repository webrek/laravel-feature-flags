<?php

namespace Webrek\FeatureFlags\Contracts;

/**
 * Implement on any object you want to evaluate features against, to control the
 * bucketing identity and the attributes available to targeting constraints.
 */
interface FeatureScope
{
    /**
     * A stable identifier used for deterministic rollout and variant bucketing.
     */
    public function featureScopeIdentifier(): string;

    /**
     * Attributes exposed to targeting constraints.
     *
     * @return array<string, mixed>
     */
    public function featureScopeAttributes(): array;
}
