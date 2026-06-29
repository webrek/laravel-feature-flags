# Laravel Feature Flags

[![Última versión en Packagist](https://img.shields.io/packagist/v/webrek/laravel-feature-flags.svg?style=flat-square)](https://packagist.org/packages/webrek/laravel-feature-flags)
[![Descargas totales](https://img.shields.io/packagist/dt/webrek/laravel-feature-flags.svg?style=flat-square)](https://packagist.org/packages/webrek/laravel-feature-flags)
[![Tests](https://img.shields.io/github/actions/workflow/status/webrek/laravel-feature-flags/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/webrek/laravel-feature-flags/actions/workflows/tests.yml)
[![Versión de PHP](https://img.shields.io/packagist/php-v/webrek/laravel-feature-flags.svg?style=flat-square)](https://php.net)
[![Licencia](https://img.shields.io/packagist/l/webrek/laravel-feature-flags.svg?style=flat-square)](LICENSE)

Feature flags para Laravel con **rollouts por porcentaje**, **targeting basado en reglas**
y **variantes A/B** — activa o desactiva funcionalidades en tiempo de ejecución sin un deploy.

## Inicio rápido

```bash
composer require webrek/laravel-feature-flags
php artisan vendor:publish --tag=feature-flags-migrations
php artisan migrate
```

```php
use Webrek\FeatureFlags\Facades\Features;

// Define una funcionalidad desplegada al 25% de los usuarios:
Features::create('new-checkout', rollout: 25);

// Verifícala (por defecto, el usuario autenticado):
if (Features::active('new-checkout')) {
    // ...
}

// O para un scope específico:
Features::for($user)->active('new-checkout');
```

```blade
@feature('new-checkout')
    <x-checkout.v2 />
@endfeature
```

## Por qué no usar tu propia columna booleana

Una columna `boolean` en una tabla de configuración responde una sola pregunta: ¿está activo para
todos? El trabajo real con funcionalidades necesita más:

- **Rollout gradual.** Lanza al 5% de los usuarios, observa tus métricas, súbelo al 25%,
  luego al 100% — y un usuario que estaba en el 5% se mantiene dentro mientras escalas, porque
  el bucketing es determinista, no aleatorio en cada request.
- **Targeting.** "Solo planes enterprise", "usuarios en MX y US", "cuentas con más
  de 30 días" — expresado como restricciones, no como ramas dispersas por todo el código.
- **Variantes A/B.** Asigna a cada usuario una variante estable (`blue` vs `green`) y
  mide cuál convierte.
- **Control en tiempo de ejecución.** Activa o desactiva un flag desde la base de datos o un comando de artisan
  sin un deploy, y desactiva al instante una funcionalidad que se comporta mal.

Este paquete hace todo eso y, a diferencia de Laravel Pennant, almacena rollouts,
restricciones y variantes como datos que puedes administrar — no solo como closures en el código.

## Definir funcionalidades

Con el store de base de datos (el predeterminado), defínelas y adminístralas en tiempo de ejecución:

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

O decláralas en el código con el **store de arreglo** (ideal para pruebas o aplicaciones
simples) — establece `FEATURE_FLAGS_STORE=array` y llena `config/feature-flags.php`:

```php
'features' => [
    'new-checkout' => ['active' => true, 'rollout' => 25],
    'button-color' => ['active' => true, 'variants' => [
        ['name' => 'blue', 'weight' => 50],
        ['name' => 'green', 'weight' => 50],
    ]],
],
```

## Verificar funcionalidades

```php
Features::active('new-checkout');             // scope por defecto (usuario autenticado)
Features::active('new-checkout', $user);       // scope explícito
Features::inactive('new-checkout', $team);
Features::variant('button-color', $user);      // 'blue' | 'green' | null
Features::for($user)->active('new-checkout');  // fluido

feature('new-checkout');                       // helper, devuelve bool
feature();                                      // helper, devuelve el manager
```

Una funcionalidad se resuelve como **activa** solo cuando pasan todas las compuertas: el interruptor maestro
está encendido, el scope cumple todas las restricciones, cae dentro del porcentaje de
rollout y (para una funcionalidad con variantes) se asigna una variante.

## Scopes

Pasa cualquier cosa como scope:

- **`null`** (u omítelo) — el usuario autenticado, con fallback a un scope global.
- Un **modelo de Eloquent** — agrupado por clase + clave; sus atributos alimentan el targeting.
- Cualquier cosa que implemente **`FeatureScope`** — tú controlas el identificador y los
  atributos expuestos a las restricciones.
- Un **string o int** — usado directamente como identidad de bucketing.

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

## Restricciones de targeting

Cada restricción es `['attribute' => ..., 'operator' => ..., 'value' => ...]` y
todas deben cumplirse. Operadores soportados:

`=` · `!=` · `in` · `not_in` · `>` · `>=` · `<` · `<=` · `contains`

```php
constraints: [
    ['attribute' => 'plan', 'operator' => 'in', 'value' => ['pro', 'enterprise']],
    ['attribute' => 'seats', 'operator' => '>=', 'value' => 10],
]
```

## Blade y middleware

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
// 404 a menos que la funcionalidad esté activa para el usuario actual
```

## Artisan

```bash
php artisan feature:list
php artisan feature:activate new-checkout
php artisan feature:deactivate new-checkout
php artisan feature:rollout new-checkout 50
```

## Dashboard

Una interfaz web integrada para activar funcionalidades, ajustar el rollout y crear o eliminar flags
en tiempo de ejecución — sin deploy, sin cliente de base de datos. Se renderiza en el servidor (sin build de JS,
sin CDN) y vive en `/feature-flags` por defecto.

```php
// config/feature-flags.php
'dashboard' => [
    'enabled' => env('FEATURE_FLAGS_DASHBOARD', true),
    'path' => 'feature-flags',
    'middleware' => ['web'],
],
```

> El dashboard controla tus flags, así que protégelo. Agrega middleware de autenticación/autorización
> (por ejemplo, `['web', 'auth', 'can:manage-features']`) y, en producción,
> restringe quién puede acceder a él. Administra el store activo, así que usa el store de
> base de datos. Publica las vistas para personalizarlas:

```bash
php artisan vendor:publish --tag=feature-flags-views
```

## Requisitos

| Componente | Versión |
| --------- | ------- |
| PHP | 8.2+ |
| Laravel | 12.x / 13.x |

## Pruebas

```bash
composer install
composer test
```

## Contribuir

Consulta [CONTRIBUTING.md](CONTRIBUTING.md).

## Seguridad

Por favor revisa la [política de seguridad](SECURITY.md) antes de reportar una
vulnerabilidad.

## Licencia

La Licencia MIT (MIT). Consulta [LICENSE](LICENSE).
