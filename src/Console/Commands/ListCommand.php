<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Yxx\LaravelPlugin\Support\Plugin;

class ListCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'plugin:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show list of all plugins.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->table([__('plugins.name'), __('plugins.status'), __('plugins.priority'), __('plugins.path')], $this->getRows());

        return 0;
    }

    /**
     * Get table rows.
     *
     * @return array
     */
    public function getRows()
    {
        $rows = [];

        /** @var Plugin $plugin */
        foreach ($this->getPlugins() as $plugin) {
            $rows[] = [
                $plugin->getName(),
                $plugin->isEnabled() ? 'Enabled' : 'Disabled',
                $plugin->get('priority'),
                $plugin->getPath(),
            ];
        }

        return $rows;
    }

    public function getPlugins()
    {
        switch ($this->option('only')) {
            case 'enabled':
                return $this->laravel['plugins.repository']->getByStatus(1);
                break;

            case 'disabled':
                return $this->laravel['plugins.repository']->getByStatus(0);
                break;

            case 'priority':
                return $this->laravel['plugins.repository']->getPriority($this->option('direction'));
                break;

            default:
                return $this->laravel['plugins.repository']->all();
                break;
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['only', 'o', InputOption::VALUE_OPTIONAL, 'Types of plugins will be displayed.', null],
            ['direction', 'd', InputOption::VALUE_OPTIONAL, 'The direction of ordering.', 'asc'],
        ];
    }
}
