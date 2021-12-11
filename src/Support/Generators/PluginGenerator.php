<?php
namespace Yxx\LaravelPlugin\Support\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Yxx\LaravelPlugin\Abstracts\Generator;
use Yxx\LaravelPlugin\Support\Config\GenerateConfigReader;
use Yxx\LaravelPlugin\Support\Repositories\RepositoryManager;
use Yxx\LaravelPlugin\Support\Stub;

class PluginGenerator extends Generator
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
     * PluginGenerator constructor.
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
     * @return PluginGenerator
     */
    public function setFilesystem(Filesystem $filesystem): PluginGenerator
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * @param  RepositoryManager  $repositoryManager
     * @return PluginGenerator
     */
    public function setRepository(RepositoryManager $repositoryManager): PluginGenerator
    {
        $this->repositoryManager = $repositoryManager;

        return $this;
    }

    /**
     * Set the laravel config instance.
     *
     * @param Config $config
     *
     * @return PluginGenerator
     */
    public function setConfig(Config $config): PluginGenerator
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Set the laravel console instance.
     *
     * @param Console $console
     *
     * @return PluginGenerator
     */
    public function setConsole(Console $console): PluginGenerator
    {
        $this->console = $console;

        return $this;
    }

    /**
     * Set force status.
     *
     * @param bool|int $force
     *
     * @return PluginGenerator
     */
    public function setForce(bool $force): PluginGenerator
    {
        $this->force = $force;

        return $this;
    }

    /**
     * Set plain flag.
     *
     * @param bool $plain
     *
     * @return PluginGenerator
     */
    public function setPlain(bool $plain): PluginGenerator
    {
        $this->plain = $plain;

        return $this;
    }

    /**
     * Set active flag.
     *
     * @param bool $active
     *
     * @return PluginGenerator
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
        return Str::lower($this->name);
    }


    /**
     * Generate the plugin.
     */
    public function generate():void
    {
        $name = $this->getName();

        $this->generateFolders();

        if ($this->plain !== true) {
            $this->generateFiles();
          //  $this->generateResources();
        }

        $this->console->info("Plugin [{$name}] created successfully.");
    }

    /**
     * Generate the folders.
     */
    public function generateFolders(): void
    {
        foreach ($this->getFolders() as $key => $folder) {
            $folder = GenerateConfigReader::read($key);

            if ($folder->generate() === false) {
                continue;
            }

            $path = $this->repositoryManager->getPluginPath($this->getName()) . '/' . $folder->getPath();

            $this->filesystem->makeDirectory($path, 0755, true);
            $this->console->info("Created : {$path}");
            $this->generateGitKeep($path);
        }
    }

    /**
     * Generate the files.
     */
    public function generateFiles(): void
    {
        foreach ($this->getFiles() as $stub => $file) {
            $path = $this->repositoryManager->getPluginPath($this->getName()) . $file;

            if (! $this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($path, $this->getStubContents($stub));

            $this->console->info("Created : {$path}");
        }
    }

    /**
     * Generate some resources.
     */
    public function generateResources()
    {
//        if (GenerateConfigReader::read('seeder')->generate() === true) {
//            $this->console->call('plugin:make-seed', [
//                'name' => $this->getStudlyName(),
//                'module' => $this->getName(),
//                '--master' => true,
//            ]);
//        }

        if (GenerateConfigReader::read('provider')->generate() === true) {
            $this->console->call('plugin:make-provider', [
                'name' => $this->getStudlyName() . 'ServiceProvider',
                'plugin' => $this->getName(),
                '--master' => true,
            ]);
            /*$this->console->call('plugin:route-provider', [
                'module' => $this->getName(),
            ]);*/
        }
//
//        if (GenerateConfigReader::read('controller')->generate() === true) {
//            $this->console->call('plugin:make-controller', [
//                'controller' => $this->getStudlyName() . 'Controller',
//                'module' => $this->getName(),
//            ]);
//        }
    }

    public function getStudlyName()
    {
        $name = explode('/', $this->name);
        $name = $name[1] ?? $name[0];

        return Str::studly($name);
    }

    /**
     * Get the contents of the specified stub file by given stub name.
     *
     * @param $stub
     *
     * @return string
     */
    protected function getStubContents($stub)
    {
        return (new Stub(
            '/' . $stub . '.stub',
            $this->getReplacement($stub)
        )
        )->render();
    }

    /**
     * get the list for the replacements.
     */
    public function getReplacements(): array
    {
        return $this->repositoryManager->config('stubs.replacements');
    }

    /**
     * Get array replacement for the specified stub.
     *
     * @param $stub
     *
     * @return array
     */
    protected function getReplacement($stub)
    {
        $replacements = $this->getReplacements();

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
            if (method_exists($this, $method = 'get' . ucfirst(Str::studly(strtolower($key))) . 'Replacement')) {
                $replaces[$key] = $this->$method();
            } else {
                $replaces[$key] = null;
            }
        }

        /*if ($stub === 'composer') {
            dd($replaces);
        }*/

        return $replaces;
    }

    /**
     * @return array
     */
    public function getFolders(): array
    {
        return $this->repositoryManager->config('paths.generator') ?? [];
    }

    /**
     * Generate git keep to the specified path.
     *
     * @param string $path
     */
    public function generateGitKeep(string $path):void
    {
        $this->filesystem->put($path . '/.gitkeep', '');
    }

    /**
     * Get the list of files will created.
     *
     * @return array
     */
    public function getFiles():array
    {
        return $this->repositoryManager->config('stubs.files');
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
     * Get the plugin name in snake case.
     *
     * @return string
     */
    protected function getSnakeNameReplacement(): string
    {
        return strtolower($this->getSnakeName());
    }

    /**
     * @return string
     */
    public function getSnakeName(): string
    {
        return Str::snake(preg_replace('/[^0-9a-z]/', '_', $this->name));
    }

    /**
     * Get replacement for $VENDOR$.
     *
     * @return string
     */
    protected function getVendorReplacement(): string
    {
        $name = explode('/', $this->getName());
        $name = $name[0];

        return $name;
    }

    /**
     * Get replacement for $MODULE_NAMESPACE$.
     *
     * @return string
     */
    protected function getPluginNamespaceReplacement(): string
    {
        $name = $this->getName();
        $namespace = ucwords(str_replace('/', ' ', $name));
        $namespace = str_replace(' ', '\\', $namespace);

        return str_replace('\\', '\\\\', $namespace);
    }

    /**
     * Get replacement for $MODULE_NAME$.
     *
     * @return string
     */
    protected function getPluginNameReplacement(): string
    {
        $name = explode('\\', $this->getPluginNamespaceReplacement());
        $name = $name[count($name) - 1];

        return $name;
    }

    /**
     * Get replacement for $AUTHOR_NAME$.
     *
     * @return string
     */
    protected function getAuthorNameReplacement(): string
    {
        $name = explode('/', $this->getName());
        $name = ucfirst($name[0]);

        return $name;
    }

    /**
     * Get replacement for $AUTHOR_EMAIL$.
     *
     * @return string
     */
    protected function getAuthorEmailReplacement(): string
    {
        return 'example@gmail.com';
    }

    protected function getProviderNamespaceReplacement(): string
    {
        return 'Providers';
    }

    protected function getPluginDomainReplacement(): string
    {
        $name = explode('/', $this->getName());
        $author = $name[0];
        $plugin = $name[1];

        return substr($author, 0, 2) . substr($plugin, 0, 2);
    }
}