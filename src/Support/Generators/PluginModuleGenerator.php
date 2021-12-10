<?php
namespace Yxx\LaravelPlugin\Support\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Yxx\LaravelPlugin\Abstracts\Generator;
use Yxx\LaravelPlugin\Support\Repositories\RepositoryManager;

class PluginModuleGenerator extends Generator
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var RepositoryManager
     */
    protected RepositoryManager $repositoryManager;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var Console
     */
    protected Console $console;

    /**
     * @var bool
     */
    protected bool $force;

    /**
     * @var bool
     */
    protected bool $plain;

    /**
     * @var bool
     */
    protected bool $isActive;

    /**
     * PluginModuleGenerator constructor.
     * @param  string  $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Set the laravel filesystem instance.
     *
     * @param Filesystem $filesystem
     *
     * @return PluginModuleGenerator
     */
    public function setFilesystem(Filesystem $filesystem): PluginModuleGenerator
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * @param  RepositoryManager  $repositoryManager
     * @return PluginModuleGenerator
     */
    public function setRepository(RepositoryManager $repositoryManager): PluginModuleGenerator
    {
        $this->repositoryManager = $repositoryManager;

        return $this;
    }

    /**
     * Set the laravel config instance.
     *
     * @param Config $config
     *
     * @return PluginModuleGenerator
     */
    public function setConfig(Config $config): PluginModuleGenerator
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Set the laravel console instance.
     *
     * @param Console $console
     *
     * @return PluginModuleGenerator
     */
    public function setConsole(Console $console): PluginModuleGenerator
    {
        $this->console = $console;

        return $this;
    }

    /**
     * Set force status.
     *
     * @param bool|int $force
     *
     * @return PluginModuleGenerator
     */
    public function setForce(bool $force): PluginModuleGenerator
    {
        $this->force = $force;

        return $this;
    }

    /**
     * Set plain flag.
     *
     * @param bool $plain
     *
     * @return PluginModuleGenerator
     */
    public function setPlain(bool $plain): PluginModuleGenerator
    {
        $this->plain = $plain;

        return $this;
    }

    /**
     * Set active flag.
     *
     * @param bool $active
     *
     * @return PluginModuleGenerator
     */
    public function setActive(bool $active): PluginModuleGenerator
    {
        $this->isActive = $active;

        return $this;
    }

    /**
     * Get the name of plugin will created. By default in studly case.
     *
     * @return string
     */
    public function getName(): string
    {
        return Str::lower($this->name);
    }


    /**
     * Generate the plugin.
     */
    public function generate():void
    {
        $name = $this->getName();

        $this->generateFolders();
    }

    /**
     * Generate the folders.
     */
    public function generateFolders()
    {
        foreach ($this->getFolders() as $key => $folder) {

        }
    }

    /**
     * @return array
     */
    public function getFolders(): array
    {
        return $this->repositoryManager->config('paths.generator') ?? [];
    }
}