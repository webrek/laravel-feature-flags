@php
    /** @var array<string, \Webrek\FeatureFlags\FeatureDefinition> $features */
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Feature Flags</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 2rem 1rem;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f8fafc; color: #0f172a;
        }
        @media (prefers-color-scheme: dark) {
            body { background: #0b1120; color: #e2e8f0; }
            .card, .field { background: #111827 !important; border-color: #1f2937 !important; }
            input { background: #0b1120 !important; color: #e2e8f0 !important; border-color: #1f2937 !important; }
            th { color: #94a3b8 !important; }
            tr { border-color: #1f2937 !important; }
        }
        .wrap { max-width: 860px; margin: 0 auto; }
        h1 { font-size: 1.25rem; font-weight: 600; margin: 0 0 1.25rem; }
        .flash { background: #dcfce7; color: #166534; padding: .6rem .9rem; border-radius: 8px; margin-bottom: 1rem; font-size: .9rem; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: .8rem 1rem; font-size: .9rem; vertical-align: middle; }
        th { text-transform: uppercase; letter-spacing: .04em; font-size: .7rem; color: #64748b; }
        tr { border-top: 1px solid #e2e8f0; }
        .name { font-weight: 600; }
        .meta { color: #94a3b8; font-size: .78rem; }
        .badge { display: inline-block; padding: .15rem .55rem; border-radius: 999px; font-size: .7rem; font-weight: 600; color: #fff; }
        .on { background: #16a34a; } .off { background: #94a3b8; }
        form.inline { display: inline; margin: 0; }
        input[type=number], input[type=text] { padding: .35rem .5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: .85rem; }
        input[type=number] { width: 4.5rem; }
        button { cursor: pointer; border: 0; border-radius: 6px; padding: .4rem .7rem; font-size: .8rem; font-weight: 600; }
        .btn { background: #2563eb; color: #fff; } .btn-muted { background: #e2e8f0; color: #0f172a; } .btn-danger { background: #dc2626; color: #fff; }
        .field { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; display: flex; gap: .6rem; align-items: center; flex-wrap: wrap; }
        .actions { display: flex; gap: .4rem; align-items: center; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Feature Flags</h1>

        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('feature-flags.store') }}" class="field">
            @csrf
            <input type="text" name="name" placeholder="feature-name" required>
            <input type="number" name="rollout" placeholder="%" min="0" max="100">
            <button type="submit" class="btn">Create feature</button>
        </form>

        <div class="card">
            <table>
                <thead>
                    <tr><th>Feature</th><th>Status</th><th>Rollout</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($features as $feature)
                        <tr>
                            <td>
                                <div class="name">{{ $feature->name }}</div>
                                @if ($feature->constraints !== [] || $feature->variants !== [])
                                    <div class="meta">
                                        @if ($feature->constraints !== []){{ count($feature->constraints) }} rule(s)@endif
                                        @if ($feature->variants !== []) · {{ implode(', ', array_column($feature->variants, 'name')) }}@endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $feature->active ? 'on' : 'off' }}">{{ $feature->active ? 'ON' : 'OFF' }}</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('feature-flags.rollout', $feature->name) }}" class="inline actions">
                                    @csrf
                                    <input type="number" name="rollout" min="0" max="100" value="{{ $feature->rollout }}" placeholder="—">
                                    <button type="submit" class="btn-muted">Save</button>
                                </form>
                            </td>
                            <td>
                                <div class="actions">
                                    <form method="POST" action="{{ route('feature-flags.toggle', $feature->name) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-muted">{{ $feature->active ? 'Disable' : 'Enable' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('feature-flags.destroy', $feature->name) }}" class="inline" onsubmit="return confirm('Delete {{ $feature->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="meta">No features yet. Create one above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
