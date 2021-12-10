<?php
namespace Yxx\LaravelPlugin\Providers;

use Illuminate\Support\ServiceProvider;

class BootstrapServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot(): void
    {
        app('plugins.repository')->boot();
    }

    /**
     * Register the provider.
     */
    public function register(): void
    {
        app('plugins.repository')->register();
    }
}