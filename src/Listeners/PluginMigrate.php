<?php

namespace Yxx\LaravelPlugin\Listeners;

use Illuminate\Support\Facades\Artisan;
use Yxx\LaravelPlugin\Support\Plugin;

class PluginMigrate
{
    public function handle(Plugin $plugin)
    {
        Artisan::call('plugin:migrate', ['plugin' => $plugin->getName()]);
    }
}
