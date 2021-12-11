<?php
namespace Yxx\LaravelPlugin\Providers;

use Illuminate\Support\ServiceProvider;
use Yxx\LaravelPlugin\Console\Commands\PluginMakeCommand;
use Yxx\LaravelPlugin\Console\Commands\ProviderMakeCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    protected array $commands = [
        PluginMakeCommand::class,
        ProviderMakeCommand::class
    ];


    public function register():void
    {
        $this->commands($this->commands);
    }
}