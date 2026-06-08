<?php

namespace Webrek\FeatureFlags\Console;

use Illuminate\Console\Command;
use Webrek\FeatureFlags\FeatureDefinition;
use Webrek\FeatureFlags\FeatureManager;

class ListFeaturesCommand extends Command
{
    protected $signature = 'feature:list';

    protected $description = 'List all defined features';

    public function handle(FeatureManager $features): int
    {
        $rows = array_map(fn (FeatureDefinition $d): array => [
            $d->name,
            $d->active ? 'on' : 'off',
            $d->rollout === null ? '—' : $d->rollout . '%',
            $d->constraints === [] ? '—' : count($d->constraints) . ' rule(s)',
            $d->variants === [] ? '—' : implode(', ', array_column($d->variants, 'name')),
        ], $features->all());

        if ($rows === []) {
            $this->info('No features defined.');

            return self::SUCCESS;
        }

        $this->table(['Feature', 'Active', 'Rollout', 'Targeting', 'Variants'], $rows);

        return self::SUCCESS;
    }
}
