<?php
namespace Yxx\LaravelPlugin\Listeners;

use Yxx\LaravelPlugin\Events\PluginInstalled;
use Yxx\LaravelPlugin\Support\Composer\PluginJsonRequire;

class ComposerRequireListener
{
    public function handle(PluginInstalled $event)
    {
        PluginJsonRequire::make($event->plugin)->__invoke();
    }
}