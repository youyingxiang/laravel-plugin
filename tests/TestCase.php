<?php
namespace Yxx\LaravelPlugin\Tests;
use Orchestra\Testbench\TestCase as Orchestra;
use Yxx\LaravelPlugin\Providers\PluginServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    private function resetDatabase()
    {
        $this->artisan('migrate:reset', [
            '--database' => 'sqlite',
        ]);
    }


    public function getEnvironmentSetup($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            PluginServiceProvider::class,
        ];
    }

    protected function setUpDatabase()
    {
        $this->resetDatabase();
    }

}