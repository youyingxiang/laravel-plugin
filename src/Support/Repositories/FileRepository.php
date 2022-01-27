<?php

namespace Yxx\LaravelPlugin\Support\Repositories;

use Exception;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\Process\Process;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Exceptions\InvalidAssetPath;
use Yxx\LaravelPlugin\Exceptions\PluginNotFoundException;
use Yxx\LaravelPlugin\Support\Collection;
use Yxx\LaravelPlugin\Support\Json;
use Yxx\LaravelPlugin\Support\Plugin;
use Yxx\LaravelPlugin\Support\Process\Installer;
use Yxx\LaravelPlugin\Support\Process\Updater;
use Yxx\LaravelPlugin\ValueObjects\ValRequires;

class FileRepository implements RepositoryInterface
{
    use Macroable;

    /**
     * @var Application
     */
    protected Application $app;

    /**
     * The plugin path.
     *
     * @var string|null
     */
    protected ?string $path;

    /**
     * The scanned paths.
     *
     * @var array
     */
    protected array $paths = [];

    /**
     * @var string
     */
    protected string $stubPath;

    /**
     * @var UrlGenerator
     */
    private UrlGenerator $url;

    /**
     * @var ConfigRepository
     */
    private ConfigRepository $config;

    /**
     * @var Filesystem
     */
    private Filesystem $files;

    /**
     * @var CacheManager
     */
    private CacheManager $cache;

    /**
     * The constructor.
     *
     * @param  Application  $app
     * @param  string|null  $path
     */
    public function __construct(Application $app, string $path = null)
    {
        $this->app = $app;
        $this->path = $path;
        $this->url = $app['url'];
        $this->config = $app['config'];
        $this->files = $app['files'];
        $this->cache = $app['cache'];
    }

    /**
     * @param  mixed  ...$args
     * @return Plugin
     */
    protected function createPlugin(...$args): Plugin
    {
        return new Plugin(...$args);
    }

    /**
     * Add other plugin location.
     *
     * @param  string  $path
     * @return $this
     */
    public function addLocation($path)
    {
        $this->paths[] = $path;

        return $this;
    }

    /**
     * Get all additional paths.
     *
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Get scanned plugins paths.
     *
     * @return array
     */
    public function getScanPaths(): array
    {
        $paths = $this->paths;

        $paths[] = $this->getPath();

        $paths = array_map(function ($path) {
            return Str::endsWith($path, '/*') ? $path : Str::finish($path, '/*');
        }, $paths);

        return $paths;
    }

    /**
     * Get & scan all plugins.
     *
     * @return array
     *
     * @throws Exception
     */
    public function scan(): array
    {
        $paths = $this->getScanPaths();

        $plugins = [];

        foreach ($paths as $key => $path) {
            $manifests = $this->getFiles()->glob("{$path}/plugin.json");

            is_array($manifests) || $manifests = [];

            foreach ($manifests as $manifest) {
                $name = Json::make($manifest)->get('name');

                $plugins[$name] = $this->createPlugin($this->app, $name, dirname($manifest));
            }
        }

        return $plugins;
    }

    /**
     * Get all plugins.
     *
     * @return array
     *
     * @throws Exception
     */
    public function all(): array
    {
        if (! $this->config('cache.enabled')) {
            return $this->scan();
        }

        return $this->formatCached($this->getCached());
    }

    /**
     * @param  string|null  $type
     * @return ValRequires
     *
     * @throws Exception
     */
    public function getComposerRequires(?string $type = null): ValRequires
    {
        $valRequires = ValRequires::make();

        return array_reduce($this->all(), function (ValRequires $valRequires, Plugin $plugin) use ($type) {
            $requires = $type ? $plugin->getComposerAttr($type) : $plugin->getAllComposerRequires();

            return $valRequires->merge($requires);
        }, $valRequires);
    }

    /**
     * @param $name
     * @param  string|null  $type
     * @return ValRequires
     *
     * @throws Exception
     */
    public function getExceptPluginNameComposerRequires($name, ?string $type = null): ValRequires
    {
        $valRequires = ValRequires::make();

        return collect($this->all())
            ->filter(fn (Plugin $plugin) => is_array($name) ? ! in_array($plugin->getName(), $name) : $plugin->getName() !== $name)
            ->reduce(function (ValRequires $valRequires, Plugin $plugin) use ($type) {
                $requires = $type ? $plugin->getComposerAttr($type) : $plugin->getAllComposerRequires();

                return $valRequires->merge($requires);
            }, $valRequires);
    }

    /**
     * Format the cached data as array of plugins.
     *
     * @param  array  $cached
     * @return array
     */
    protected function formatCached(array $cached): array
    {
        $plugins = [];

        foreach ($cached as $name => $plugin) {
            $path = $plugin['path'];

            $plugins[$name] = $this->createPlugin($this->app, $name, $path);
        }

        return $plugins;
    }

    /**
     * Get cached plugins.
     *
     * @return array
     */
    public function getCached(): array
    {
        return $this->cache->remember($this->config('cache.key'), $this->config('cache.lifetime'), function () {
            return $this->toCollection()->toArray();
        });
    }

    /**
     * Get all plugins as collection instance.
     *
     * @return Collection
     *
     * @throws Exception
     */
    public function toCollection(): Collection
    {
        return new Collection($this->scan());
    }

    /**
     * Get plugins by status.
     *
     * @param  bool  $status
     * @return array
     *
     * @throws Exception
     */
    public function getByStatus(bool $status): array
    {
        $plugins = [];

        /** @var Plugin $plugin */
        foreach ($this->all() as $name => $plugin) {
            if ($plugin->isStatus($status)) {
                $plugins[$name] = $plugin;
            }
        }

        return $plugins;
    }

    /**
     * Determine whether the given plugins exist.
     *
     * @param $name
     * @return bool
     *
     * @throws Exception
     */
    public function has($name): bool
    {
        return array_key_exists($name, $this->all());
    }

    /**
     * Get list of enabled plugins.
     *
     * @return array
     *
     * @throws Exception
     */
    public function allEnabled(): array
    {
        return $this->getByStatus(true);
    }

    /**
     * Get list of disabled plugins.
     *
     * @return array
     *
     * @throws Exception
     */
    public function allDisabled(): array
    {
        return $this->getByStatus(false);
    }

    /**
     * Get count from all plugins.
     *
     * @return int
     *
     * @throws Exception
     */
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * Get all ordered plugins.
     *
     * @param  string  $direction
     * @return array
     *
     * @throws Exception
     */
    public function getOrdered($direction = 'asc'): array
    {
        $plugins = $this->allEnabled();

        uasort($plugins, function (Plugin $a, Plugin $b) use ($direction) {
            if ($a->get('priority') === $b->get('priority')) {
                return 0;
            }

            if ($direction === 'desc') {
                return $a->get('priority') < $b->get('priority') ? 1 : -1;
            }

            return $a->get('priority') > $b->get('priority') ? 1 : -1;
        });

        return $plugins;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path ?: $this->config('paths.plugins', base_path('plugins'));
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function register(): void
    {
        /** @var Plugin $plugin */
        foreach ($this->getOrdered() as $plugin) {
            $plugin->register();
        }
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function boot(): void
    {
        foreach ($this->getOrdered() as $plugin) {
            $plugin->boot();
        }
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function find(string $name): ?Plugin
    {
        foreach ($this->all() as $plugin) {
            if ($plugin->getLowerName() === strtolower($name)) {
                return $plugin;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function findByAlias(string $alias): ?Plugin
    {
        foreach ($this->all() as $plugin) {
            if ($plugin->getAlias() === $alias) {
                return $plugin;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function findRequirements($name): array
    {
        $requirements = [];

        $plugin = $this->findOrFail($name);

        foreach ($plugin->getRequires() as $requirementName) {
            $requirements[] = $this->findByAlias($requirementName);
        }

        return $requirements;
    }

    /**
     * Find a specific plugin, if there return that, otherwise throw exception.
     *
     * @param $name
     * @return Plugin
     *
     * @throws PluginNotFoundException
     * @throws Exception
     */
    public function findOrFail(string $name): Plugin
    {
        $plugin = $this->find($name);

        if ($plugin !== null) {
            return $plugin;
        }

        throw new PluginNotFoundException("Plugin [{$name}] does not exist!");
    }

    /**
     * Get all plugin as laravel collection instance.
     *
     * @param $status
     * @return Collection
     *
     * @throws Exception
     */
    public function collections($status = 1): Collection
    {
        return new Collection($this->getByStatus($status));
    }

    /**
     * Get plugin path for a specific plugin.
     *
     * @param  string  $pluginName
     * @return string
     *
     * @throws Exception
     */
    public function getPluginPath(string $pluginName): string
    {
        try {
            return $this->findOrFail($pluginName)->getPath().'/';
        } catch (PluginNotFoundException $e) {
            return $this->getPath().'/'.Str::studly($pluginName).'/';
        }
    }

    /**
     * @inheritDoc
     */
    public function assetPath(string $plugin): string
    {
        return $this->config('paths.assets').'/'.$plugin;
    }

    /**
     * @inheritDoc
     */
    public function config(string $key, $default = null)
    {
        return $this->config->get('plugins.'.$key, $default);
    }

    /**
     * Get storage path for plugins used.
     *
     * @return string
     */
    public function getUsedStoragePath(): string
    {
        $directory = storage_path('app/plugins');
        if ($this->getFiles()->exists($directory) === false) {
            $this->getFiles()->makeDirectory($directory, 0777, true);
        }

        $path = storage_path('app/plugins/plugins.used');
        if (! $this->getFiles()->exists($path)) {
            $this->getFiles()->put($path, '');
        }

        return $path;
    }

    /**
     * Set plugins used for cli session.
     *
     * @param $name
     *
     * @throws PluginNotFoundException
     */
    public function setUsed($name)
    {
        $plugin = $this->findOrFail($name);

        $this->getFiles()->put($this->getUsedStoragePath(), $plugin);
    }

    /**
     * Forget the plugins used for cli session.
     */
    public function forgetUsed()
    {
        if ($this->getFiles()->exists($this->getUsedStoragePath())) {
            $this->getFiles()->delete($this->getUsedStoragePath());
        }
    }

    /**
     * Get plugins used for cli session.
     *
     * @return string
     *
     * @throws PluginNotFoundException|FileNotFoundException
     */
    public function getUsedNow(): string
    {
        return $this->findOrFail($this->getFiles()->get($this->getUsedStoragePath()));
    }

    /**
     * Get laravel filesystem instance.
     *
     * @return Filesystem
     */
    public function getFiles(): Filesystem
    {
        return $this->files;
    }

    /**
     * Get plugins assets path.
     *
     * @return string
     */
    public function getAssetsPath(): string
    {
        return $this->config('paths.assets');
    }

    /**
     * Get asset url from a specific plugins.
     *
     * @param  string  $asset
     * @return string
     *
     * @throws InvalidAssetPath
     */
    public function asset(string $asset): string
    {
        if (Str::contains($asset, ':') === false) {
            throw InvalidAssetPath::missingPluginName($asset);
        }
        [$name, $url] = explode(':', $asset);

        $baseUrl = str_replace(public_path().DIRECTORY_SEPARATOR, '', $this->getAssetsPath());

        $url = $this->url->asset($baseUrl."/{$name}/".$url);

        return str_replace(['http://', 'https://'], '//', $url);
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(string $name): bool
    {
        return $this->findOrFail($name)->isEnabled();
    }

    /**
     * @inheritDoc
     */
    public function isDisabled(string $name): bool
    {
        return ! $this->isEnabled($name);
    }

    /**
     * Enabling a specific plugin.
     *
     * @param  string  $name
     * @return void
     *
     * @throws PluginNotFoundException
     */
    public function enable(string $name): void
    {
        $this->findOrFail($name)->enable();
    }

    /**
     * Disabling a specific plugin.
     *
     * @param  string  $name
     * @return void
     *
     * @throws PluginNotFoundException
     */
    public function disable(string $name): void
    {
        $this->findOrFail($name)->disable();
    }

    /**
     * @inheritDoc
     */
    public function delete(string $name): bool
    {
        $plugin = $this->findOrFail($name);

        return $plugin->delete();
    }

    /**
     * Update dependencies for the specified plugin.
     *
     * @param  string  $plugin
     */
    public function update(string $plugin)
    {
        with(new Updater($this))->update($plugin);
    }

    /**
     * Install the specified plugin.
     *
     * @param  string  $name
     * @param  string  $version
     * @param  string  $type
     * @param  bool  $subtree
     * @return Process
     */
    public function install(string $name, string $version = 'dev-master', string $type = 'composer', bool $subtree = false): Process
    {
        $installer = new Installer($name, $version, $type, $subtree);

        return $installer->run();
    }

    /**
     * Get stub path.
     *
     * @return string|null
     */
    public function getStubPath(): ?string
    {
        if (isset($this->stubPath)) {
            return $this->stubPath;
        }

        if ($this->config('stubs.enabled') === true) {
            return $this->config('stubs.path') ?? __DIR__.'/../../../stubs';
        }

        return optional($this)->stubPath;
    }

    /**
     * Set stub path.
     *
     * @param  string  $stubPath
     * @return $this
     */
    public function setStubPath(string $stubPath): FileRepository
    {
        $this->stubPath = $stubPath;

        return $this;
    }
}
