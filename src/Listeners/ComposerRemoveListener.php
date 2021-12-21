<?php
namespace Yxx\LaravelPlugin\Listeners;

use Yxx\LaravelPlugin\Events\PluginDeleted;
use Yxx\LaravelPlugin\Support\Composer\PluginJsonRemove;

class ComposerRemoveListener
{
    public function handle(PluginDeleted $event)
    {
        PluginJsonRemove::make($event->plugin)->__invoke();
    }
}