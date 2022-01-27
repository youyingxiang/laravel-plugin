<?php

namespace Yxx\LaravelPlugin\Support\Generators;

use Exception;
use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Yxx\LaravelPlugin\Contracts\ActivatorInterface;
use Yxx\LaravelPlugin\Contracts\GeneratorInterface;
use Yxx\LaravelPlugin\Support\Config\GenerateConfigReader;
use Yxx\LaravelPlugin\Support\Repositories\FileRepository;
use Yxx\LaravelPlugin\Support\Stub;

class PluginGenerator implements GeneratorInterface
{
    /**
     * The plugin name will created.
     *
     * @var string
     */
    protected string $name;

    /**
     * The laravel config instance.
     *
     * @var Config|null
     */
    protected ?Config $config;

    /**
     * The laravel filesystem instance.
     *
     * @var Filesystem|null
     */
    protected ?Filesystem $filesystem;

    /**
     * The laravel console instance.
     *
     * @var Console|null
     */
    protected ?Console $console;

    /**
     * The activator instance.
     *
     * @var ActivatorInterface|null
     */
    protected ?ActivatorInterface $activator;

    /**
     * The plugin instance.
     *
     * @var FileRepository|null
     */
    protected ?FileRepository $pluginRepository;

    /**
     * Force status.
     *
     * @var bool
     */
    protected bool $force = false;

    /**
     * Enables the plugin.
     *
     * @var bool
     */
    protected bool $isActive = false;

    /**
     * The constructor.
     *
     * @param  string  $name
     * @param  FileRepository|null  $pluginRepository
     * @param  Config|null  $config
     * @param  Filesystem|null  $filesystem
     * @param  Console|null  $console
     * @param  ActivatorInterface|null  $activator
     */
    public function __construct(
        string $name,
        FileRepository $pluginRepository = null,
        Config $config = null,
        Filesystem $filesystem = null,
        Console $console = null,
        ActivatorInterface $activator = null
    ) {
        $this->name = $name;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->console = $console;
        $this->pluginRepository = $pluginRepository;
        $this->activator = $activator;
    }

    /**
     * Set active flag.
     *
     * @param  bool  $active
     * @return $this
     */
    public function setActive(bool $active): PluginGenerator
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
        return Str::studly($this->name);
    }

    /**
     * Get the laravel config instance.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Set the laravel config instance.
     *
     * @param  Config  $config
     * @return $this
     */
    public function setConfig(Config $config): PluginGenerator
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Set the plugins activator.
     *
     * @param  ActivatorInterface  $activator
     * @return $this
     */
    public function setActivator(ActivatorInterface $activator): PluginGenerator
    {
        $this->activator = $activator;

        return $this;
    }

    /**
     * Get the laravel filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Set the laravel filesystem instance.
     *
     * @param  Filesystem  $filesystem
     * @return $this
     */
    public function setFilesystem(Filesystem $filesystem): PluginGenerator
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Get the laravel console instance.
     *
     * @return Console
     */
    public function getConsole(): Console
    {
        return $this->console;
    }

    /**
     * Set the laravel console instance.
     *
     * @param  Console  $console
     * @return $this
     */
    public function setConsole(Console $console): PluginGenerator
    {
        $this->console = $console;

        return $this;
    }

    /**
     * Get the FileRepository instance.
     *
     * @return FileRepository $plugin
     */
    public function getPluginRepository(): FileRepository
    {
        return $this->pluginRepository;
    }

    /**
     * Set the plugin instance.
     *
     * @param  FileRepository  $pluginRepository
     * @return $this
     */
    public function setPluginRepository(FileRepository $pluginRepository): PluginGenerator
    {
        $this->pluginRepository = $pluginRepository;

        return $this;
    }

    /**
     * Get the list of folders will created.
     *
     * @return array
     */
    public function getFolders(): array
    {
        return $this->pluginRepository->config('paths.generator');
    }

    /**
     * Get the list of files will created.
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->pluginRepository->config('stubs.files');
    }

    /**
     * Set force status.
     *
     * @param  bool|int  $force
     * @return $this
     */
    public function setForce(bool $force): PluginGenerator
    {
        $this->force = $force;

        return $this;
    }

    /**
     * Generate the plugin.
     *
     * @throws Exception
     */
    public function generate(): int
    {
        $name = $this->getName();

        if ($this->pluginRepository->has($name)) {
            if ($this->force) {
                $this->pluginRepository->delete($name);
            } else {
                $this->console->error("Plugin [{$name}] already exist!");

                return E_ERROR;
            }
        }

        $this->generateFolders();

        $this->generatePluginJsonFile();

        $this->generateFiles();

        $this->generateResources();

        $this->activator->setActiveByName($name, $this->isActive);

        $this->console->info("Plugin [{$name}] created successfully.");

        return 0;
    }

    public function generateResources()
    {
        if (GenerateConfigReader::read('seeder')->generate() === true) {
            $this->console->call('plugin:make-seed', [
                'name'     => $this->getName(),
                'plugin'   => $this->getName(),
                '--master' => true,
            ]);
        }

        if (GenerateConfigReader::read('provider')->generate() === true) {
            $this->console->call('plugin:make-provider', [
                'name'     => $this->getName().'ServiceProvider',
                'plugin'   => $this->getName(),
                '--master' => true,
            ]);
            $this->console->call('plugin:route-provider', [
                'plugin' => $this->getName(),
            ]);
        }

        if (GenerateConfigReader::read('controller')->generate() === true) {
            $this->console->call('plugin:make-controller', [
                'controller' => $this->getName().'Controller',
                'plugin'     => $this->getName(),
            ]);
        }
    }

    /**
     * Generate the plugin.json file.
     *
     * @throws Exception
     */
    private function generatePluginJsonFile()
    {
        $path = $this->pluginRepository->getPluginPath($this->getName()).'plugin.json';

        if (! $this->filesystem->isDirectory($dir = dirname($path))) {
            $this->filesystem->makeDirectory($dir, 0775, true);
        }

        $this->filesystem->put($path, $this->getStubContents('json'));

        $this->console->info("Created : {$path}");
    }

    /**
     * Generate the folders.
     *
     * @throws Exception
     */
    public function generateFolders(): void
    {
        foreach ($this->getFolders() as $key => $folder) {
            $folder = GenerateConfigReader::read($key);

            if ($folder->generate() === false) {
                continue;
            }

            $path = $this->pluginRepository->getPluginPath($this->getName()).$folder->getPath();

            $this->filesystem->makeDirectory($path, 0755, true);
            if (config('plugins.stubs.gitkeep')) {
                $this->generateGitKeep($path);
            }
        }
    }

    /**
     * Generate the files.
     *
     * @throws Exception
     */
    public function generateFiles(): void
    {
        foreach ($this->getFiles() as $stub => $file) {
            $path = $this->pluginRepository->getPluginPath($this->getName()).$file;

            if (! $this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($path, $this->getStubContents($stub));

            $this->console->info("Created : {$path}");
        }
    }

    /**
     * Generate git keep to the specified path.
     *
     * @param  string  $path
     */
    public function generateGitKeep(string $path): void
    {
        $this->filesystem->put($path.'/.gitkeep', '');
    }

    /**
     * Get the contents of the specified stub file by given stub name.
     *
     * @param  string  $stub
     * @return string
     */
    protected function getStubContents(string $stub): string
    {
        return (new Stub(
            '/'.$stub.'.stub',
            $this->getReplacement($stub)
        )
        )->render();
    }

    /**
     * Get array replacement for the specified stub.
     *
     * @param  string  $stub
     * @return array
     */
    protected function getReplacement(string $stub): array
    {
        $replacements = $this->pluginRepository->config('stubs.replacements');

        if (! isset($replacements[$stub])) {
            return [];
        }

        $keys = $replacements[$stub];

        $replaces = [];

        if ($stub === 'json' || $stub === 'composer') {
            if (in_array('PROVIDER_NAMESPACE', $keys, true) === false) {
                $keys[] = 'PROVIDER_NAMESPACE';
            }
        }
        foreach ($keys as $key) {
            if (method_exists($this, $method = 'get'.ucfirst(Str::studly(strtolower($key))).'Replacement')) {
                $replaces[$key] = $this->$method();
            } else {
                $replaces[$key] = null;
            }
        }

        return $replaces;
    }

    /**
     * Get the plugin name in lower case.
     *
     * @return string
     */
    protected function getLowerNameReplacement(): string
    {
        return strtolower($this->getName());
    }

    /**
     * Get the plugin name in studly case.
     *
     * @return string
     */
    protected function getStudlyNameReplacement(): string
    {
        return $this->getName();
    }

    /**
     * Get replacement for $PLUGIN_NAMESPACE$.
     *
     * @return string
     */
    protected function getPluginNamespaceReplacement(): string
    {
        return str_replace('\\', '\\\\', $this->pluginRepository->config('namespace'));
    }

    /**
     * @return string
     */
    protected function getProviderNamespaceReplacement(): string
    {
        return str_replace('\\', '\\\\', GenerateConfigReader::read('provider')->getNamespace());
    }
}
