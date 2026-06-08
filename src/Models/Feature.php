<?php

namespace Webrek\FeatureFlags\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property bool $active
 * @property int|null $rollout
 * @property array<int, array<string, mixed>>|null $constraints
 * @property array<int, array<string, mixed>>|null $variants
 */
class Feature extends Model
{
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'bool',
        'rollout' => 'integer',
        'constraints' => 'array',
        'variants' => 'array',
    ];

    public function getTable(): string
    {
        return config('feature-flags.table', 'features');
    }
}
