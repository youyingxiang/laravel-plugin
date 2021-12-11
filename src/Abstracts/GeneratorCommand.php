<?php

namespace Yxx\LaravelPlugin\Abstracts;

use Illuminate\Console\Command;
use Yxx\LaravelPlugin\Exceptions\FileAlreadyExistException;
use Yxx\LaravelPlugin\Support\Generators\FileGenerator;

abstract class GeneratorCommand extends Command
{
    /**
     * The name of 'name' argument.
     *
     * @var string
     */
    protected $argumentName = '';

    /**
     * Get template contents.
     *
     * @return string
     */
    abstract protected function getTemplateContents();

    /**
     * Get the destination file path.
     *
     * @return string
     */
    abstract protected function getDestinationFilePath();

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = str_replace('\\', '/', $this->getDestinationFilePath());

        if (! $this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }

        $contents = $this->getTemplateContents();

        try {
            $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;
            (new FileGenerator($path, $contents))
                ->withFileOverwrite($overwriteFile)
                ->generate();

            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException $e) {
            $this->error("File : {$path} already exists.");
        }
    }

    /**
     * Get class name.
     *
     * @return string
     */
    public function getClass(): string
    {
        return class_basename($this->argument($this->argumentName));
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        return '';
    }

    /**
     * Get class namespace.
     *
     * @param  Plugin  $plugin
     * @return string
     */
    public function getClassNamespace(Plugin $plugin): string
    {
        $extra = str_replace($this->getClass(), '', $this->argument($this->argumentName));
        $extra = str_replace('/', '\\', $extra);
        $namespace = '';
        $namespace .= '\\' . $plugin->getStudlyName();
        $namespace .= '\\' . $this->getDefaultNamespace();
        $namespace .= '\\' . $extra;
        $namespace = str_replace('/', '\\', $namespace);

        return trim($namespace, '\\');
    }

    public function getPluginNamespace(Plugin $plugin): string
    {
        return str_replace('/', '\\', $plugin->getStudlyName());
    }

    public function getDomainName()
    {
        $plugin = $this->laravel['plugins']->find($this->getPluginName());

        return $plugin->getExtraJuzaweb('domain');
    }
}
