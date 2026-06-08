<?php

namespace Webrek\FeatureFlags\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webrek\FeatureFlags\FeatureManager;

/**
 * Aborts with 404 unless the named feature is active for the default scope
 * (the authenticated user, when present).
 *
 *     Route::get('/beta', ...)->middleware('feature:new-dashboard');
 */
class EnsureFeatureIsActive
{
    public function __construct(private readonly FeatureManager $features) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if ($this->features->inactive($feature)) {
            abort(404);
        }

        return $next($request);
    }
}
