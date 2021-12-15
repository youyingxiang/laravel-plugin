<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Yxx\LaravelPlugin\Support\Config\GenerateConfigReader;
use Yxx\LaravelPlugin\Support\Stub;
use Yxx\LaravelPlugin\Traits\PluginCommandTrait;

class RouteProviderMakeCommand extends GeneratorCommand
{
    use PluginCommandTrait;

    protected string $argumentName = 'plugin';

    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'plugin:route-provider';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Create a new route service provider for the specified plugin.';

    /**
     * The command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['plugin', InputArgument::OPTIONAL, 'The name of plugin will be used.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when the file already exists.'],
        ];
    }

    /**
     * Get template contents.
     *
     * @return string
     */
    protected function getTemplateContents(): string
    {
        $plugin = $this->getPlugin();

        return (new Stub('/route-provider.stub', [
            'NAMESPACE'            => $this->getClassNamespace($plugin),
            'CLASS'                => $this->getFileName(),
            'PLUGIN_NAMESPACE'     => $this->laravel['plugins.repository']->config('namespace'),
            'PLUGIN'               => $this->getPluginName(),
            'CONTROLLER_NAMESPACE' => $this->getControllerNameSpace(),
            'WEB_ROUTES_PATH'      => $this->getWebRoutesPath(),
            'API_ROUTES_PATH'      => $this->getApiRoutesPath(),
            'LOWER_NAME'           => $plugin->getLowerName(),
        ]))->render();
    }

    /**
     * @return string
     */
    private function getFileName(): string
    {
        return 'RouteServiceProvider';
    }

    /**
     * Get the destination file path.
     *
     * @return string
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
    protected function getWebRoutesPath(): string
    {
        return '/'.$this->laravel['plugins.repository']->config('stubs.files.routes/web', 'Routes/web.php');
    }

    /**
     * @return string
     */
    protected function getApiRoutesPath(): string
    {
        return '/'.$this->laravel['plugins.repository']->config('stubs.files.routes/api', 'Routes/api.php');
    }

    public function getDefaultNamespace(): string
    {
        $repository = $this->laravel['plugins.repository'];

        return $repository->config('paths.generator.provider.namespace') ?: $repository->config('paths.generator.provider.path', 'Providers');
    }

    /**
     * @return string
     */
    private function getControllerNameSpace(): string
    {
        $repository = $this->laravel['plugins.repository'];

        return str_replace('/', '\\', $repository->config('paths.generator.controller.namespace') ?: $repository->config('paths.generator.controller.path', 'Controller'));
    }
}
