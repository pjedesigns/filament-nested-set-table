<?php

namespace Pjedesigns\FilamentNestedSetTable;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Pjedesigns\FilamentNestedSetTable\Services\TreeMover;
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
            ->hasViews(static::$viewNamespace)
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(TreeMover::class, fn () => new TreeMover);
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            assets: $this->getAssets(),
            package: 'pjedesigns/filament-nested-set-table'
        );
    }

    /**
     * @return array<\Filament\Support\Assets\Asset>
     */
    protected function getAssets(): array
    {
        return [
            AlpineComponent::make(
                'filament-nested-set-table',
                __DIR__.'/../resources/dist/filament-nested-set-table.js'
            ),
            Css::make(
                'filament-nested-set-table-styles',
                __DIR__.'/../resources/dist/filament-nested-set-table.css'
            ),
        ];
    }
}
