<?php
namespace Yxx\LaravelPlugin\Support\Repositories;

use Illuminate\Container\Container;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Exceptions\PluginNotFoundException;
use Yxx\LaravelPlugin\Support\Plugin;

class RepositoryManager
{
    /**
     * @var Container
     */
    protected Container $app;

    /**
     * @var RepositoryInterface
     */
    protected RepositoryInterface $repository;


    public function __construct(Container $app, RepositoryInterface $repository)
    {
        $this->app = $app;
        $this->repository = $repository;
    }

    /**
     * Get all ordered plugins.
     *
     * @param string $direction
     *
     * @return array
     */
    public function getOrdered($direction = 'asc'): array
    {
        return $this->repository->getOrdered();
    }

    /**
     * @void
     */
    public function register():void
    {
        foreach ($this->getOrdered() as $module) {
            $module->register();
        }
    }

    /**
     * @void
     */
    public function boot(): void
    {
        foreach ($this->getOrdered() as $module) {
            $module->boot();
        }
    }

    /**
     * @param  string  $key
     * @param  string|null  $default
     * @return mixed
     */
    public function config(string $key, ?string $default = null)
    {
        return $this->repository->config($key, $default);
    }

    /**
     * @param  string  $plugin
     * @return string
     */
    public function getPluginPath(string $plugin): string
    {
        return $this->repository->getPluginPath($plugin);
    }

    /**
     * @param  string  $name
     * @return Plugin
     * @throws PluginNotFoundException
     */
    public function findOrFail(string $name): Plugin
    {
        return $this->repository->findOrFail($name);
    }


}