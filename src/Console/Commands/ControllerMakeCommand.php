<?php
namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Yxx\LaravelPlugin\Support\Config\GenerateConfigReader;
use Yxx\LaravelPlugin\Support\Stub;
use Yxx\LaravelPlugin\Traits\PluginCommandTrait;

class ControllerMakeCommand extends GeneratorCommand
{
    use PluginCommandTrait;

    /**
     * The name of argument being used.
     *
     * @var string
     */
    protected string $argumentName = 'controller';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'plugin:make-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new restful controller for the specified plugin.';

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getDestinationFilePath(): string
    {
        $path = $this->getPlugin()->getPath() . "/";

        $controllerPath = GenerateConfigReader::read('controller');

        return $path . $controllerPath->getPath() . '/' . $this->getControllerName() . '.php';
    }

    /**
     * @return string
     */
    protected function getTemplateContents(): string
    {
        $plugin = $this->getPlugin();

        return (new Stub($this->getStubName(), [
            'PLUGINNAME'        => $plugin->getStudlyName(),
            'CONTROLLERNAME'    => $this->getControllerName(),
            'NAMESPACE'         => $plugin->getStudlyName(),
            'CLASS_NAMESPACE'   => $this->getClassNamespace($plugin),
            'CLASS'             => $this->getControllerNameWithoutNamespace(),
            'LOWER_NAME'        => $plugin->getLowerName(),
            'PLUGIN'            => $this->getPluginName(),
            'NAME'              => $this->getPluginName(),
            'STUDLY_NAME'       => $plugin->getStudlyName(),
            'PLUGIN_NAMESPACE'  => $this->laravel['plugins.repository']->config('namespace'),
        ]))->render();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['controller', InputArgument::REQUIRED, 'The name of the controller class.'],
            ['plugin', InputArgument::OPTIONAL, 'The name of plugins will be used.'],
        ];
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['plain', 'p', InputOption::VALUE_NONE, 'Generate a plain controller', null],
            ['api', null, InputOption::VALUE_NONE, 'Exclude the create and edit methods from the controller.'],
        ];
    }

    /**
     * @return string
     */
    protected function getControllerName(): string
    {
        $controller = Str::studly($this->argument('controller'));

        if (Str::contains(strtolower($controller), 'controller') === false) {
            $controller .= 'Controller';
        }

        return $controller;
    }

    /**
     * @return array|string
     */
    private function getControllerNameWithoutNamespace()
    {
        return class_basename($this->getControllerName());
    }

    public function getDefaultNamespace() : string
    {
        $repository = $this->laravel['plugins.repository'];

        return $repository->config('paths.generator.controller.namespace') ?: $repository->config('paths.generator.controller.path', 'Http/Controllers');
    }

    /**
     * Get the stub file name based on the options
     * @return string
     */
    protected function getStubName(): string
    {
        if ($this->option('plain') === true) {
            $stub = '/controller-plain.stub';
        } elseif ($this->option('api') === true) {
            $stub = '/controller-api.stub';
        } else {
            $stub = '/controller.stub';
        }

        return $stub;
    }
}