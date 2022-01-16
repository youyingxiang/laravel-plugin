<?php

namespace Yxx\LaravelPlugin\Listeners;

use Illuminate\Support\Facades\Artisan;
use Yxx\LaravelPlugin\Support\Plugin;

class PluginPublish
{
    public function handle(Plugin $plugin)
    {
        dd($plugin);
        Artisan::call('plugin:publish', ['plugin' => $plugin->getName()]);
    }
}
