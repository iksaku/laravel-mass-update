<?php

namespace Iksaku\Laravel\MassUpdate\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Iksaku\\Laravel\\MassUpdate\\Tests\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
