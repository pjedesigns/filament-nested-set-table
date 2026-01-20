<?php

namespace Pjedesigns\FilamentNestedSetTable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentNestedSetTableServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-nested-set-table';

    public static string $viewNamespace = 'filament-nested-set-table';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews(static::$viewNamespace);
    }

    public function packageBooted(): void
    {
        //
    }
}
