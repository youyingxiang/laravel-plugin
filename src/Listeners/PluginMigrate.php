<?php
namespace Yxx\LaravelPlugin\Listeners;

use Illuminate\Support\Facades\Artisan;
use Yxx\LaravelPlugin\Events\PluginInstalled;

class PluginMigrate
{
    public function handle(PluginInstalled $installed)
    {
        Artisan::call("plugin:migrate", ["plugin" => $installed->plugin->getName()]);
    }
}