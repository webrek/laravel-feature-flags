<?php

namespace Webrek\FeatureFlags\Console;

use Illuminate\Console\Command;
use Webrek\FeatureFlags\FeatureManager;

class ActivateFeatureCommand extends Command
{
    protected $signature = 'feature:activate {name}';

    protected $description = 'Turn a feature on';

    public function handle(FeatureManager $features): int
    {
        $name = (string) $this->argument('name');
        $features->activate($name);

        $this->info("Feature [{$name}] is now active.");

        return self::SUCCESS;
    }
}
