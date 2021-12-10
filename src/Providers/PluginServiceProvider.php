<?php
namespace Yxx\LaravelPlugin\Providers;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Yxx\LaravelPlugin\Contracts\PluginStatusInterface;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Support\Repositories\FileRepository;
use Yxx\LaravelPlugin\Support\Repositories\RepositoryManager;

class PluginServiceProvider extends ServiceProvider
{
    public function register():void
    {
        $this->registerNamespaces();
        $this->registerServices();
        $this->registerProviders();
    }

    protected function registerNamespaces():void
    {
        $configPath = __DIR__ . '/../../config/plugin.php';
        $this->mergeConfigFrom($configPath, 'plugins');
    }


    protected function registerServices():void
    {
        $this->app->singleton('plugins.repository',  function ($app){
            return new RepositoryManager($app, $this->createRepository($app));
        });
        //$this->app->singleton('plugin.status', PluginStatusInterface::class);
    }



    protected function setupStubPath():void
    {

    }

    protected function registerProviders():void
    {
        $this->app->register(ConsoleServiceProvider::class);
    }

    /**
     * @param  Container  $app
     * @return RepositoryInterface
     */
    protected function createRepository(Container $app):RepositoryInterface
    {
        $repository = config('plugins.default');
        switch ($repository) {
            case 'file':
                return new FileRepository($app, config('plugins.plugin_path'));
                break;
            default:
                break;
        }
        throw new \InvalidArgumentException("Unsupported driver [{$repository}]");
    }

    public function boot():void
    {
        $this->app->register(BootstrapServiceProvider::class);
    }

}