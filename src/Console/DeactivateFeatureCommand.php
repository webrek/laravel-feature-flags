<?php

namespace Webrek\FeatureFlags\Console;

use Illuminate\Console\Command;
use Webrek\FeatureFlags\FeatureManager;

class DeactivateFeatureCommand extends Command
{
    protected $signature = 'feature:deactivate {name}';

    protected $description = 'Turn a feature off';

    public function handle(FeatureManager $features): int
    {
        $name = (string) $this->argument('name');
        $features->deactivate($name);

        $this->info("Feature [{$name}] is now inactive.");

        return self::SUCCESS;
    }
}
