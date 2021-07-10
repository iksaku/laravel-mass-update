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
        config()->set('database.default', env('DATABASE_CONNECTION', 'sqlite'));

        $database_connections = [
            'sqlite' => [
                'database' => ':memory:'
            ],
            'mysql' => [
                'database' => 'test',
                'username' => 'root',
                'password' => 'password',
            ],
            'pgsql' => [
                'database' => 'test',
                'username' => 'root',
                'password' => 'password',
            ],
            'sqlsrv' => [
                'database' => 'test',
                'username' => 'root',
                'password' => 'Password!',
            ]
        ];

        foreach ($database_connections as $connection => $configuration) {
            foreach ($configuration as $key => $value) {
                config()->set("database.connections.$connection.$key", $value);
            }
        }
    }

    public function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
