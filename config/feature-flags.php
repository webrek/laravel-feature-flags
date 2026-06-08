<?php

use Webrek\FeatureFlags\Models\Feature;

return [

    /*
    |--------------------------------------------------------------------------
    | Default store
    |--------------------------------------------------------------------------
    |
    | Where feature definitions live. "database" lets you flip flags at runtime
    | without a deploy; "array" reads them from the `features` key below, which
    | is handy for tests or apps that prefer declaring flags in code.
    |
    */

    'default' => env('FEATURE_FLAGS_STORE', 'database'),

    'stores' => [
        'database' => [
            'driver' => 'database',
            'model' => Feature::class,
        ],

        'array' => [
            'driver' => 'array',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database table
    |--------------------------------------------------------------------------
    */

    'table' => 'features',

    /*
    |--------------------------------------------------------------------------
    | Code-defined features (array store)
    |--------------------------------------------------------------------------
    |
    | Used when the active store is "array". Example:
    |
    |   'new-checkout' => ['active' => true, 'rollout' => 25],
    |   'button-color' => ['active' => true, 'variants' => [
    |       ['name' => 'blue', 'weight' => 50],
    |       ['name' => 'green', 'weight' => 50],
    |   ]],
    |   'enterprise-export' => ['active' => true, 'constraints' => [
    |       ['attribute' => 'plan', 'operator' => 'in', 'value' => ['pro', 'enterprise']],
    |   ]],
    |
    */

    'features' => [],

];
