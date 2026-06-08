<?php

namespace Webrek\FeatureFlags\Http;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webrek\FeatureFlags\FeatureManager;

class DashboardController
{
    public function __construct(private readonly FeatureManager $features) {}

    public function index(): Response
    {
        return response()->view('feature-flags::dashboard', [
            'features' => $this->features->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rollout' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $this->features->create($data['name'], rollout: $data['rollout'] ?? null);

        return back()->with('status', "Feature [{$data['name']}] created.");
    }

    public function toggle(string $feature): RedirectResponse
    {
        $definition = $this->features->all()[$feature] ?? null;

        if ($definition !== null) {
            $definition->active
                ? $this->features->deactivate($feature)
                : $this->features->activate($feature);
        }

        return back();
    }

    public function rollout(Request $request, string $feature): RedirectResponse
    {
        $data = $request->validate([
            'rollout' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $this->features->rollout($feature, $data['rollout'] ?? null);

        return back();
    }

    public function destroy(string $feature): RedirectResponse
    {
        $this->features->forget($feature);

        return back()->with('status', "Feature [{$feature}] deleted.");
    }
}
