<?php
namespace Yxx\LaravelPlugin\Support\Repositories;

use Illuminate\Container\Container;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;

class RepositoryManager
{
    /**
     * @var Container
     */
    protected Container $app;

    /**
     * @var RepositoryInterface
     */
    protected RepositoryInterface $repositor;


    public function __construct(Container $app, RepositoryInterface $repository)
    {
        $this->app = $app;
        $this->repositor = $repository;
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
        return $this->repositor->getOrdered();
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
        return $this->repositor->config($key, $default);
    }


}