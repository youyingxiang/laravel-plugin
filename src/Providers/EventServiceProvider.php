<?php

namespace Yxx\LaravelPlugin\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Yxx\LaravelPlugin\Events\PluginDeleted;
use Yxx\LaravelPlugin\Events\PluginInstalled;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array|array[]
     */
    protected $listen = [
        PluginInstalled::class => [
        ],
        PluginDeleted::class => [
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
