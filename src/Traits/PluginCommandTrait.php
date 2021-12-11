<?php

namespace Yxx\LaravelPlugin\Traits;

use Yxx\LaravelPlugin\Exceptions\FileAlreadyExistException;
use Yxx\LaravelPlugin\Support\Generators\FileGenerator;
use Yxx\LaravelPlugin\Support\Plugin;
use Yxx\LaravelPlugin\Support\Stub;

trait PluginCommandTrait
{
    /**
     * Get the plugin name.
     *
     * @return string
     */
    public function getPluginName(): string
    {
        $plugin = $this->argument('plugin');
        /** @var Plugin $plugin */
        $plugin = app('plugins.repository')->findOrFail($plugin);

        return $plugin->getName();
    }

    /**
     * @param $path
     * @param $contents
     */
    protected function makeFile(string $path, string $contents): void
    {
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
     * @param $file
     * @param $data
     * @return string
     */
    protected function stubRender($file, $data): string
    {
        return (new Stub('/' . $file, $data))->render();
    }
}
