<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Yxx\LaravelPlugin\Support\Migrations\Migrator;
use Yxx\LaravelPlugin\Support\Plugin;

class MigrateCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'plugin:migrate';

    /**
     * @var string
     */
    protected $description = 'Migrate the migrations from the specified plugin or from all plugins.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $name = $this->argument('plugin');

        if ($name) {
            $plugin = $this->laravel['plugins.repository']->findOrFail($name);

            $this->migrate($plugin);

            return 0;
        }
        /** @var Plugin $plugin */
        foreach ($this->laravel['plugins.repository']->getOrdered($this->option('direction')) as $plugin) {
            $this->line('Running for plugin: <info>'.$plugin->getName().'</info>');

            $this->migrate($plugin);
        }

        return 0;
    }

    protected function migrate(Plugin $plugin): void
    {
        $path = str_replace(base_path(), '', (new Migrator($plugin, $this->getLaravel()))->getPath());

        if ($this->option('subpath')) {
            $path = $path.'/'.$this->option('subpath');
        }

        $this->call('migrate', [
            '--path' => $path,
            '--database' => $this->option('database'),
            '--pretend' => $this->option('pretend'),
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['plugin', InputArgument::OPTIONAL, 'The name of plugin will be used.'],
        ];
    }

    /**
     * @return array[]
     */
    protected function getOptions(): array
    {
        return [
            ['direction', 'd', InputOption::VALUE_OPTIONAL, 'The direction of ordering.', 'asc'],
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'],
            ['subpath', null, InputOption::VALUE_OPTIONAL, 'Indicate a subpath to run your migrations from'],
        ];
    }
}
