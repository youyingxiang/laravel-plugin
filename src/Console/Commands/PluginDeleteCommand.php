<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Yxx\LaravelPlugin\Support\Composer\ComposerRemove;
use Yxx\LaravelPlugin\Traits\PluginCommandTrait;

class PluginDeleteCommand extends Command
{
    use PluginCommandTrait;

    protected $name = 'plugin:delete';

    protected $description = 'Delete a plugin from the application';

    public function handle(): int
    {
        try {
            ComposerRemove::make()->appendRemovePluginRequires(
                $this->getPluginName(),
                $this->getPlugin()->getAllComposerRequires()
            )->run();

            $this->laravel['plugins.repository']->delete($this->argument('plugin'));

            $this->info("Plugin {$this->argument('plugin')} has been deleted.");

            return 0;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        }
    }

    protected function getArguments(): array
    {
        return [
            ['plugin', InputArgument::REQUIRED, 'The name of plugin to delete.'],
        ];
    }
}
