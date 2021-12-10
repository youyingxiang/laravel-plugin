<?php
namespace Yxx\LaravelPlugin\Providers;

use Illuminate\Support\ServiceProvider;
use Yxx\LaravelPlugin\Console\Commands\PluginMakeCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    protected array $commands = [
        PluginMakeCommand::class
    ];


    public function register():void
    {
        $this->commands($this->commands);
    }
}