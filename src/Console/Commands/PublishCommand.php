<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Yxx\LaravelPlugin\Support\Plugin;
use Yxx\LaravelPlugin\Support\Publishing\AssetPublisher;

class PublishCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'plugin:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish a plugin\'s assets to the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($name = $this->argument('plugin')) {
            $plugin = $this->laravel['plugins.repository']->findOrFail($name);
            $this->publish($plugin);

            return 0;
        }
        $this->publishAll();

        return 0;
    }

    /**
     * Publish assets from all plugins.
     */
    public function publishAll(): void
    {
        /** @var Plugin $plugin */
        foreach ($this->laravel['plugins.repository']->allEnabled() as $plugin) {
            $this->publish($plugin);
        }
    }

    /**
     * Publish assets from the specified plugin.
     *
     * @param  Plugin  $plugin
     */
    public function publish(Plugin $plugin): void
    {
        with(new AssetPublisher($plugin))
            ->setRepository($this->laravel['plugins.repository'])
            ->setConsole($this)
            ->publish();

        $this->line("<info>Published</info>: {$plugin->getStudlyName()}");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['plugin', InputArgument::OPTIONAL, 'The name of plugin will be used.'],
        ];
    }
}
