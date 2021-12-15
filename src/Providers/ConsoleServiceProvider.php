<?php

namespace Yxx\LaravelPlugin\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Support\Str;
use Yxx\LaravelPlugin\Console\Commands\ControllerMakeCommand;
use Yxx\LaravelPlugin\Console\Commands\DisableCommand;
use Yxx\LaravelPlugin\Console\Commands\EnableCommand;
use Yxx\LaravelPlugin\Console\Commands\ListCommand;
use Yxx\LaravelPlugin\Console\Commands\PluginCommand;
use Yxx\LaravelPlugin\Console\Commands\PluginDeleteCommand;
use Yxx\LaravelPlugin\Console\Commands\PluginMakeCommand;
use Yxx\LaravelPlugin\Console\Commands\ProviderMakeCommand;
use Yxx\LaravelPlugin\Console\Commands\RouteProviderMakeCommand;
use Yxx\LaravelPlugin\Console\Commands\SeedMakeCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Namespace of the console commands.
     *
     * @var string
     */
    protected string $consoleNamespace = 'Yxx\\LaravelPlugin\\Console\\Commands';

    /**
     * The available commands.
     *
     * @var array
     */
    protected array $commands = [
        PluginCommand::class,
        PluginMakeCommand::class,
        ProviderMakeCommand::class,
        RouteProviderMakeCommand::class,
        ControllerMakeCommand::class,
        SeedMakeCommand::class,
        ListCommand::class,
        DisableCommand::class,
        EnableCommand::class,
        PluginDeleteCommand::class,
    ];

    /**
     * @return array
     */
    private function resolveCommands(): array
    {
        $commands = [];

        foreach ((config('plugins.commands') ?: $this->commands) as $command) {
            $commands[] = Str::contains($command, $this->consoleNamespace) ?
                $command :
                $this->consoleNamespace.'\\'.$command;
        }

        return $commands;
    }

    public function register(): void
    {
        $this->commands($this->resolveCommands());
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return $this->commands;
    }
}
