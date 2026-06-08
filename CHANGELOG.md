# Changelog

All notable changes to `webrek/laravel-feature-flags` are documented here. The
format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and the
project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-06-07

### Added

- Feature manager with `active()`, `inactive()`, `variant()` and a fluent
  `for($scope)` helper.
- Deterministic percentage rollouts and weighted A/B variants, stable per scope.
- Rule-based targeting constraints (`=`, `!=`, `in`, `not_in`, `>`, `>=`, `<`,
  `<=`, `contains`).
- Database and array stores, selectable via config.
- Default scope resolution from the authenticated user, plus the `FeatureScope`
  contract for custom scopes.
- `@feature` Blade directive, a `feature` route middleware and the `feature()`
  helper / `Features` facade.
- Artisan commands: `feature:list`, `feature:activate`, `feature:deactivate`,
  `feature:rollout`.
- Publishable migration for the database store.
