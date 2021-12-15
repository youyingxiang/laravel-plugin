<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Yxx\LaravelPlugin\Support\Config\GenerateConfigReader;
use Yxx\LaravelPlugin\Support\Stub;
use Yxx\LaravelPlugin\Traits\CanClearPluginsCache;
use Yxx\LaravelPlugin\Traits\PluginCommandTrait;

class SeedMakeCommand extends GeneratorCommand
{
    use PluginCommandTrait;
    use CanClearPluginsCache;

    protected string $argumentName = 'name';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'plugin:make-seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new seeder for the specified plugin.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of seeder will be created.'],
            ['plugin', InputArgument::OPTIONAL, 'The name of plugin will be used.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'master',
                null,
                InputOption::VALUE_NONE,
                'Indicates the seeder will created is a master database seeder.',
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getTemplateContents(): string
    {
        $plugin = $this->getPlugin();

        return (new Stub('/seeder.stub', [
            'NAME'      => $this->getSeederName(),
            'PLUGIN'    => $this->getPluginName(),
            'NAMESPACE' => $this->getClassNamespace($plugin),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath(): string
    {
        $this->clearCache();

        $path = $this->getPlugin()->getPath().'/';

        $seederPath = GenerateConfigReader::read('seeder');

        return $path.$seederPath->getPath().'/'.$this->getSeederName().'.php';
    }

    /**
     * Get seeder name.
     *
     * @return string
     */
    private function getSeederName(): string
    {
        $end = $this->option('master') ? 'DatabaseSeeder' : 'TableSeeder';

        return Str::studly($this->argument('name')).$end;
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        $repository = $this->laravel['plugins.repository'];

        return $repository->config('paths.generator.seeder.namespace') ?: $repository->config('paths.generator.seeder.path', 'Database/Seeders');
    }
}
