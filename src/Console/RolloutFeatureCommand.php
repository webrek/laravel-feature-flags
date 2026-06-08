<?php

namespace Webrek\FeatureFlags\Console;

use Illuminate\Console\Command;
use Webrek\FeatureFlags\FeatureManager;

class RolloutFeatureCommand extends Command
{
    protected $signature = 'feature:rollout {name} {percentage : An integer between 0 and 100}';

    protected $description = 'Set the percentage rollout for a feature';

    public function handle(FeatureManager $features): int
    {
        $name = (string) $this->argument('name');
        $percentage = (int) $this->argument('percentage');

        $features->rollout($name, $percentage);

        $this->info("Feature [{$name}] is now rolled out to {$percentage}% of scopes.");

        return self::SUCCESS;
    }
}
