<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Yxx\LaravelPlugin\Support\Plugin;

class DisableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'plugin:disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable the specified plugin.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /**
         * check if user entred an argument.
         */
        if ($this->argument('plugin') === null) {
            $this->disableAll();
        }

        /** @var Plugin $plugin */
        $plugin = $this->laravel['plugins.repository']->findOrFail($this->argument('plugin'));

        if ($plugin->isEnabled()) {
            $plugin->disable();

            $this->info("Plugin [{$plugin}] disabled successful.");
        } else {
            $this->comment("Plugin [{$plugin}] has already disabled.");
        }

        return 0;
    }

    /**
     * disableAll.
     *
     * @return void
     */
    public function disableAll(): void
    {
        $plugins = $this->laravel['plugins.repository']->all();
        /** @var Plugin $plugin */
        foreach ($plugins as $plugin) {
            if ($plugin->isEnabled()) {
                $plugin->disable();

                $this->info("Plugin [{$plugin}] disabled successful.");
            } else {
                $this->comment("Plugin [{$plugin}] has already disabled.");
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['plugin', InputArgument::OPTIONAL, 'Plugin name.'],
        ];
    }
}
