<?php

namespace Yxx\LaravelPlugin\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Yxx\LaravelPlugin\Providers\PluginServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        if (method_exists($this, 'withoutMockingConsoleOutput')) {
            $this->withoutMockingConsoleOutput();
        }
    }


    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'plugins-testing');
        $app['config']->set('database.connections.plugins-testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }


    protected function getPackageProviders($app)
    {
        return [
            PluginServiceProvider::class,
        ];
    }

}
