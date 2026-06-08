<?php

use Webrek\FeatureFlags\FeatureManager;

if (! function_exists('feature')) {
    /**
     * Without arguments, returns the feature manager. With a name, returns
     * whether the feature is active for the given (or default) scope.
     */
    function feature(?string $name = null, mixed $scope = null): FeatureManager|bool
    {
        $manager = app(FeatureManager::class);

        if ($name === null) {
            return $manager;
        }

        return $manager->active($name, $scope);
    }
}
