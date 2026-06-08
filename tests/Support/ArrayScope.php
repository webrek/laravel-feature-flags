<?php

namespace Webrek\FeatureFlags\Tests\Support;

use Webrek\FeatureFlags\Contracts\FeatureScope;

class ArrayScope implements FeatureScope
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        private readonly string $identifier,
        private readonly array $attributes = [],
    ) {}

    public function featureScopeIdentifier(): string
    {
        return $this->identifier;
    }

    public function featureScopeAttributes(): array
    {
        return $this->attributes;
    }
}
