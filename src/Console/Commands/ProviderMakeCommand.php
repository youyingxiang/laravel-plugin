<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Yxx\LaravelPlugin\Abstracts\GeneratorCommand;
use Yxx\LaravelPlugin\Support\Config\GenerateConfigReader;
use Yxx\LaravelPlugin\Support\Plugin;
use Yxx\LaravelPlugin\Support\Stub;
use Yxx\LaravelPlugin\Traits\PluginCommandTrait;

class ProviderMakeCommand extends GeneratorCommand
{
    use PluginCommandTrait;

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'name';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'plugin:make-provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service provider class for the specified plugin.';


    public function getDefaultNamespace(): string
    {
        return 'Providers';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The service provider name.'],
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
            ['master', null, InputOption::VALUE_NONE, 'Indicates the master service provider', null],
        ];
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $stub = 'provider';

        /** @var Plugin $plugin */
        $plugin = $this->laravel['plugins.repository']->findOrFail($this->getPluginName());

        return (new Stub('/' . $stub . '.stub', [
            'NAMESPACE' => $this->getClassNamespace($plugin),
            'CLASS' => $this->getClass(),
            'LOWER_NAME' => $plugin->getLowerName(),
            'PLUGIN' => $this->getPluginName(),
            'NAME' => $this->getFileName(),
            'STUDLY_NAME' => $plugin->getStudlyName(),
            'PLUGIN_NAMESPACE' => $this->getPluginNamespace($plugin),
            'PATH_VIEWS' => GenerateConfigReader::read('views')->getPath(),
            'PATH_LANG' => GenerateConfigReader::read('lang')->getPath(),
            'PATH_CONFIG' => GenerateConfigReader::read('config')->getPath(),
            'MIGRATIONS_PATH' => GenerateConfigReader::read('migration')->getPath(),
            'FACTORIES_PATH' => GenerateConfigReader::read('factory')->getPath(),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath(): string
    {
        $path = $this->laravel['plugins.repository']->getPluginPath($this->getPluginName());

        $generatorPath = GenerateConfigReader::read('provider');

        return $path . $generatorPath->getPath() . '/' . $this->getFileName() . '.php';
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('name'));
    }
}
