<?php
namespace Yxx\LaravelPlugin\Providers;

use Illuminate\Support\ServiceProvider;
use Yxx\LaravelPlugin\Contracts\ActivatorInterface;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Exceptions\InvalidActivatorClass;
use Yxx\LaravelPlugin\Support\FileRepository;
use Yxx\LaravelPlugin\Support\Stub;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
        $this->registerNamespaces();
        $this->registerPlugins();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
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

    /**
     * Register package's namespaces.
     */
    protected function registerNamespaces(): void
    {
        $configPath = __DIR__ . '/../../config/config.php';

        $this->mergeConfigFrom($configPath, 'plugins');
        $this->publishes([
            $configPath => config_path('plugins.php'),
        ], 'config');
    }

    /**
     * Setup stub path.
     */
    public function setupStubPath(): void
    {
        $path = $this->app['config']->get('plugin.stubs.path') ?? __DIR__ . '/../../stubs';
        Stub::setBasePath($path);

        $this->app->booted(function ($app) {
            /** @var RepositoryInterface $pluginRepository */
            $pluginRepository = $app[RepositoryInterface::class];
            if ($pluginRepository->config('stubs.enabled') === true) {
                Stub::setBasePath($pluginRepository->config('stubs.path'));
            }
        });
    }

    protected function registerServices():void
    {
        $this->app->singleton(RepositoryInterface::class, function ($app) {
            $path = $app['config']->get('plugins.paths.plugins');

            return new FileRepository($app, $path);
        });
        $this->app->singleton(ActivatorInterface::class, function ($app) {
            $activator = $app['config']->get('plugins.activator');
            $class = $app['config']->get('plugins.activators.' . $activator)['class'];

            if ($class === null) {
                throw InvalidActivatorClass::missingConfig();
            }

            return new $class($app);
        });
        $this->app->alias(RepositoryInterface::class, 'plugins.repository');
    }

    /**
     * Register providers.
     */
    protected function registerProviders():void
    {
        $this->app->register(ConsoleServiceProvider::class);
        $this->app->register(ContractsServiceProvider::class);
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




}