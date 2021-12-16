<?php

namespace PluginsTest\PluginOne\Providers;

use Illuminate\Support\ServiceProvider;

class PluginOneServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected bool $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        app()->bind('foo', function () {
            return 'fooFun';
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [];
    }
}
