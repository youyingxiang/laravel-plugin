<?php

namespace Yxx\LaravelPlugin\Providers;

use Illuminate\Support\ServiceProvider;
use Yxx\LaravelPlugin\Contracts\ActivatorInterface;
use Yxx\LaravelPlugin\Contracts\ClientInterface;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Exceptions\InvalidActivatorClass;
use Yxx\LaravelPlugin\Support\Repositories\FileRepository;
use Yxx\LaravelPlugin\Support\Stub;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
        $this->registerPlugins();
        $this->registerPublishing();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'plugins');
        $this->setPsr4();
        $this->registerServices();
        $this->setupStubPath();
        $this->registerProviders();
    }

    /**
     * Register all plugins.
     */
    protected function registerPlugins(): void
    {
        $this->app->register(BootstrapServiceProvider::class);
    }

    protected function setPsr4(): void
    {
        if (file_exists(base_path('/vendor/autoload.php'))) {
            $loader = require base_path('/vendor/autoload.php');
            $namespace = $this->app['config']->get('plugins.namespace');
            $path = $this->app['config']->get('plugins.paths.plugins');
            $loader->setPsr4("{$namespace}\\", ["{$path}/"]);
        }
    }

    /**
     * Setup stub path.
     */
    public function setupStubPath(): void
    {
        $path = $this->app['config']->get('plugin.stubs.path') ?? __DIR__.'/../../stubs';
        Stub::setBasePath($path);

        $this->app->booted(function ($app) {
            /** @var RepositoryInterface $pluginRepository */
            $pluginRepository = $app[RepositoryInterface::class];
            if ($pluginRepository->config('stubs.enabled') === true) {
                Stub::setBasePath($pluginRepository->config('stubs.path'));
            }
        });
    }

    protected function registerServices(): void
    {
        $this->app->singleton(RepositoryInterface::class, function ($app) {
            $path = $app['config']->get('plugins.paths.plugins');

            return new FileRepository($app, $path);
        });
        $this->app->singleton(ActivatorInterface::class, function ($app) {
            $activator = $app['config']->get('plugins.activator');
            $class = $app['config']->get('plugins.activators.'.$activator)['class'];

            if ($class === null) {
                throw InvalidActivatorClass::missingConfig();
            }

            return new $class($app);
        });
        $this->app->singleton(ClientInterface::class, function ($app) {
            $class = $app['config']->get('plugins.market.default');
            if ($class === null) {
                throw InvalidActivatorClass::missingConfig();
            }

            return new $class();
        });
        $this->app->alias(RepositoryInterface::class, 'plugins.repository');
        $this->app->alias(ActivatorInterface::class, 'plugins.activator');
        $this->app->alias(ClientInterface::class, 'plugins.client');
    }

    /**
     * Register providers.
     */
    protected function registerProviders(): void
    {
        $this->app->register(ConsoleServiceProvider::class);
        $this->app->register(ContractsServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [RepositoryInterface::class, 'plugins.repository'];
    }

    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/config.php' => config_path('plugins.php'),
            ], 'laravel-plugin-config');
            $this->publishes([
                __DIR__.'/../../resources/lang' => resource_path('lang'),
            ], 'laravel-plugin-lang');

            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
    }
}
