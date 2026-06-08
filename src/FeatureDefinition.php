<?php

namespace Webrek\FeatureFlags;

/**
 * The stored configuration of a single feature: its master switch, optional
 * percentage rollout, targeting constraints and A/B variants.
 */
final class FeatureDefinition
{
    /**
     * @param  list<array{attribute: string, operator?: string, value?: mixed}>  $constraints
     * @param  list<array{name: string, weight: int}>  $variants
     */
    public function __construct(
        public readonly string $name,
        public readonly bool $active = true,
        public readonly ?int $rollout = null,
        public readonly array $constraints = [],
        public readonly array $variants = [],
    ) {}

    /**
     * @param  array{active?: bool, rollout?: int|null, constraints?: list<array<string, mixed>>, variants?: list<array<string, mixed>>}  $attributes
     */
    public static function fromArray(string $name, array $attributes): self
    {
        return new self(
            $name,
            (bool) ($attributes['active'] ?? true),
            isset($attributes['rollout']) ? (int) $attributes['rollout'] : null,
            $attributes['constraints'] ?? [],
            $attributes['variants'] ?? [],
        );
    }
}
