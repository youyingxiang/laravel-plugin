<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Yxx\LaravelPlugin\Support\Config\GenerateConfigReader;
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
    protected string $argumentName = 'name';

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
        $repository = $this->laravel['plugins.repository'];

        return $repository->config('paths.generator.provider.namespace') ?: $repository->config('paths.generator.provider.path', 'Providers');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
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
    protected function getOptions(): array
    {
        return [
            ['master', null, InputOption::VALUE_NONE, 'Indicates the master service provider', null],
        ];
    }

    /**
     * @return string
     */
    protected function getTemplateContents(): string
    {
        $stub = $this->option('master') ? 'scaffold/provider' : 'provider';

        $plugin = $this->getPlugin();

        return (new Stub('/'.$stub.'.stub', [
            'NAMESPACE'         => $this->getClassNamespace($plugin),
            'CLASS'             => $this->getClass(),
            'LOWER_NAME'        => $plugin->getLowerName(),
            'PLUGIN'            => $this->getPluginName(),
            'NAME'              => $this->getFileName(),
            'STUDLY_NAME'       => $plugin->getStudlyName(),
            'PLUGIN_NAMESPACE'  => $this->laravel['plugins.repository']->config('namespace'),
            'PATH_VIEWS'        => GenerateConfigReader::read('views')->getPath(),
            'PATH_LANG'         => GenerateConfigReader::read('lang')->getPath(),
            'PATH_CONFIG'       => GenerateConfigReader::read('config')->getPath(),
            'MIGRATIONS_PATH'   => GenerateConfigReader::read('migration')->getPath(),
            'FACTORIES_PATH'    => GenerateConfigReader::read('factory')->getPath(),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath(): string
    {
        $path = $this->getPlugin()->getPath().'/';

        $generatorPath = GenerateConfigReader::read('provider');

        return $path.$generatorPath->getPath().'/'.$this->getFileName().'.php';
    }

    /**
     * @return string
     */
    private function getFileName(): string
    {
        return Str::studly($this->argument('name'));
    }
}
