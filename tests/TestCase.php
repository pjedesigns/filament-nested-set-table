<?php

namespace Pjedesigns\FilamentNestedSetTable\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Pjedesigns\FilamentNestedSetTable\FilamentNestedSetTableServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            FilamentNestedSetTableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
