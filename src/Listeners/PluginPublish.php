<?php
namespace Yxx\LaravelPlugin\Listeners;

use Illuminate\Support\Facades\Artisan;
use Yxx\LaravelPlugin\Events\PluginInstalled;

class PluginPublish
{
    public function handle(PluginInstalled $installed)
    {
        Artisan::call("plugin:publish", ["plugin" => $installed->plugin->getName()]);
    }
}