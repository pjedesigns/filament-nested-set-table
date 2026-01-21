<?php

namespace Pjedesigns\FilamentNestedSetTable\Tests;

use Filament\FilamentServiceProvider;
use Filament\Support\SupportServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Pjedesigns\FilamentNestedSetTable\FilamentNestedSetTableServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            FilamentServiceProvider::class,
            NestedSetServiceProvider::class,
            FilamentNestedSetTableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up a view path for testing
        config()->set('view.paths', [
            __DIR__.'/../resources/views',
            resource_path('views'),
        ]);
    }
}
