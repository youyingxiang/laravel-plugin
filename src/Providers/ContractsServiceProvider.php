<?php

namespace Yxx\LaravelPlugin\Providers;

use Carbon\Laravel\ServiceProvider;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Support\Repositories\FileRepository;

class ContractsServiceProvider extends ServiceProvider
{
    /**
     * Register some binding.
     */
    public function register()
    {
        $this->app->bind(RepositoryInterface::class, FileRepository::class);
    }
}
