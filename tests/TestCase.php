<?php

namespace Iksaku\Laravel\MassUpdate\Tests;

use Illuminate\Contracts\Config\Repository;
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

    public function defineEnvironment($app): void
    {
        tap($app['config'], function (Repository $config) {
            $config->set('database.connections.sqlite.database', ':memory:');

            if ($config->get('database.default') === 'mariadb' && ! $config->has('database.connections.mariadb')) {
                $config->set('database.default', 'mysql');
            }

            // Fix collations
            $config->set('database.connections.mysql.collation', 'utf8mb4_unicode_ci');
            $config->set('database.connections.mariadb.collation', 'utf8mb4_unicode_ci');
        });
    }

    public function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
