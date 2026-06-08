# Laravel Feature Flags

[![Latest Version on Packagist](https://img.shields.io/packagist/v/webrek/laravel-feature-flags.svg?style=flat-square)](https://packagist.org/packages/webrek/laravel-feature-flags)
[![Total Downloads](https://img.shields.io/packagist/dt/webrek/laravel-feature-flags.svg?style=flat-square)](https://packagist.org/packages/webrek/laravel-feature-flags)
[![Tests](https://img.shields.io/github/actions/workflow/status/webrek/laravel-feature-flags/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/webrek/laravel-feature-flags/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/webrek/laravel-feature-flags.svg?style=flat-square)](https://php.net)
[![License](https://img.shields.io/packagist/l/webrek/laravel-feature-flags.svg?style=flat-square)](LICENSE)

Feature flags for Laravel with **percentage rollouts**, **rule-based targeting**
and **A/B variants** — flip features at runtime without a deploy.

## Quickstart

```bash
composer require webrek/laravel-feature-flags
php artisan vendor:publish --tag=feature-flags-migrations
php artisan migrate
```

```php
use Webrek\FeatureFlags\Facades\Features;

// Define a feature rolled out to 25% of users:
Features::create('new-checkout', rollout: 25);

// Check it (defaults to the authenticated user):
if (Features::active('new-checkout')) {
    // ...
}

// Or for a specific scope:
Features::for($user)->active('new-checkout');
```

```blade
@feature('new-checkout')
    <x-checkout.v2 />
@endfeature
```

## Why not roll your own boolean column

A `boolean` column on a settings table answers one question: is this on for
everyone? Real feature work needs more:

- **Gradual rollout.** Ship to 5% of users, watch your metrics, raise it to 25%,
  then 100% — and a user who was in the 5% stays in as you climb, because
  bucketing is deterministic, not random per request.
- **Targeting.** "Enterprise plans only", "users in MX and US", "accounts older
  than 30 days" — expressed as constraints, not branches scattered through code.
- **A/B variants.** Assign each user a stable variant (`blue` vs `green`) and
  measure which converts.
- **Runtime control.** Flip a flag from the database or an artisan command
  without a deploy, and kill a misbehaving feature instantly.

This package does all of that, and unlike Laravel Pennant it stores rollouts,
constraints and variants as data you can manage — not just closures in code.

## Defining features

With the database store (the default), define and manage at runtime:

```php
Features::create(
    'enterprise-export',
    active: true,
    constraints: [
        ['attribute' => 'plan', 'operator' => 'in', 'value' => ['pro', 'enterprise']],
    ],
);

Features::create('button-color', variants: [
    ['name' => 'blue',  'weight' => 50],
    ['name' => 'green', 'weight' => 50],
]);

Features::activate('new-checkout');
Features::deactivate('new-checkout');
Features::rollout('new-checkout', 50);
Features::forget('old-flag');
```

Or declare them in code with the **array store** (great for tests or simple
apps) — set `FEATURE_FLAGS_STORE=array` and fill `config/feature-flags.php`:

```php
'features' => [
    'new-checkout' => ['active' => true, 'rollout' => 25],
    'button-color' => ['active' => true, 'variants' => [
        ['name' => 'blue', 'weight' => 50],
        ['name' => 'green', 'weight' => 50],
    ]],
],
```

## Checking features

```php
Features::active('new-checkout');             // default scope (auth user)
Features::active('new-checkout', $user);       // explicit scope
Features::inactive('new-checkout', $team);
Features::variant('button-color', $user);      // 'blue' | 'green' | null
Features::for($user)->active('new-checkout');  // fluent

feature('new-checkout');                       // helper, returns bool
feature();                                      // helper, returns the manager
```

A feature resolves to **active** only when every gate passes: the master switch
is on, the scope matches all constraints, it falls within the rollout
percentage, and (for a variant feature) a variant is assigned.

## Scopes

Pass anything as a scope:

- **`null`** (or omit) — the authenticated user, falling back to a global scope.
- An **Eloquent model** — bucketed by class + key; its attributes feed targeting.
- Anything implementing **`FeatureScope`** — you control the identifier and the
  attributes exposed to constraints.
- A **string or int** — used directly as the bucketing identity.

```php
use Webrek\FeatureFlags\Contracts\FeatureScope;

class Team extends Model implements FeatureScope
{
    public function featureScopeIdentifier(): string
    {
        return 'team:' . $this->id;
    }

    public function featureScopeAttributes(): array
    {
        return ['plan' => $this->plan, 'seats' => $this->seats];
    }
}
```

## Targeting constraints

Each constraint is `['attribute' => ..., 'operator' => ..., 'value' => ...]` and
all must pass. Supported operators:

`=` · `!=` · `in` · `not_in` · `>` · `>=` · `<` · `<=` · `contains`

```php
constraints: [
    ['attribute' => 'plan', 'operator' => 'in', 'value' => ['pro', 'enterprise']],
    ['attribute' => 'seats', 'operator' => '>=', 'value' => 10],
]
```

## Blade & middleware

```blade
@feature('new-dashboard')
    <x-dashboard.v2 />
@endfeature

@unlessfeature('new-dashboard')
    <x-dashboard.v1 />
@endfeature
```

```php
Route::get('/beta', BetaController::class)->middleware('feature:new-dashboard');
// 404 unless the feature is active for the current user
```

## Artisan

```bash
php artisan feature:list
php artisan feature:activate new-checkout
php artisan feature:deactivate new-checkout
php artisan feature:rollout new-checkout 50
```

## Requirements

| Component | Version |
| --------- | ------- |
| PHP | 8.2+ |
| Laravel | 12.x |

## Testing

```bash
composer install
composer test
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Security

Please review the [security policy](SECURITY.md) before reporting a
vulnerability.

## License

The MIT License (MIT). See [LICENSE](LICENSE).
