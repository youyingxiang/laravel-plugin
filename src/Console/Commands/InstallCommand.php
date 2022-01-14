<?php

namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Yxx\LaravelPlugin\Contracts\ActivatorInterface;
use Yxx\LaravelPlugin\Support\Generators\LocalInstallGenerator;

class InstallCommand extends Command
{
    protected $name = 'plugin:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the plugin through the file directory.';

    /**
     * @return int
     */
    public function handle(): int
    {
        $path = $this->argument('path');
        try {
            $code = LocalInstallGenerator::make()
                ->setLocalPath($path)
                ->setFilesystem($this->laravel['files'])
                ->setPluginRepository($this->laravel['plugins.repository'])
                ->setActivator($this->laravel[ActivatorInterface::class])
                ->setActive(! $this->option('disabled'))
                ->setConsole($this)
                ->generate();

            return $code;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        }
    }

    protected function getArguments(): array
    {
        return [
            ['path', InputArgument::REQUIRED, 'Local path.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['disabled', 'd', InputOption::VALUE_NONE, 'Do not enable the plugin at creation.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when the plugin already exists.'],
        ];
    }
}
