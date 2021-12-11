<?php
namespace Yxx\LaravelPlugin\Support\Repositories;

use Exception;
use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Exceptions\PluginNotFoundException;
use Yxx\LaravelPlugin\Support\Json;
use Yxx\LaravelPlugin\Support\Plugin;

class FileRepository implements RepositoryInterface
{
    use Macroable;

    /**
     * @var Container
     */
    protected Container $app;

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
     * FileRepository constructor.
     * @param  Container  $app
     * @param  string|null  $path
     */
    public function __construct(Container $app, ?string $path = null)
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
    public function createPlugin(...$args): Plugin
    {
        return new Plugin(...$args);
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
     * Get all ordered plugins.
     *
     * @param string $direction
     *
     * @return array
     */
    public function getOrdered($direction = 'asc'): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path ?: base_path('plugins');
    }

    /**
     * @param  string  $key
     * @param  string|null  $default
     * @return mixed
     */
    public function config(string $key, ?string $default = null)
    {
        return $this->config->get('plugins.' . $key, $default);
    }

    /**
     * @param  string  $plugin
     * @return string
     */
    public function getPluginPath(string $plugin): string
    {
        try {
            return $this->findOrFail($plugin)->getPath() . '/';
        } catch (PluginNotFoundException $exception) {
            return $this->getPath() . '/' . Str::lower($plugin) . '/';
        }

    }

    /**
     * Find a specific plugin, if there return that, otherwise throw exception.
     *
     * @param $name
     *
     * @return Plugin
     *
     * @throws PluginNotFoundException
     */
    public function findOrFail($name): Plugin
    {
       $plugin = $this->find($name);

       if ($plugin !== null) {
            return $plugin;
       }

       throw new PluginNotFoundException("Plugin [{$name}] does not exist!");
    }

    /**
     * @param $name
     * @return Plugin|null
     * @throws Exception
     */
    public function find($name): ?Plugin
    {
        foreach ($this->all() as $plugin) {
            if ($plugin->getLowerName() === strtolower($name)) {
                return $plugin;
            }
        }

        return null;
    }

    /**
     * Get all plugins.
     *
     * @return array
     * @throws Exception
     */
    public function all():array
    {
        if (! $this->config('cache.enabled')) {
            return $this->scan();
        }
    }

    /**
     * Get scanned plugins paths.
     *
     * @return array
     */
    public function getScanPaths(): array
    {
        $paths = $this->paths;
        $paths[] = $this->getPath() . '/*/*';

        $paths = array_map(function ($path) {
            return Str::endsWith($path, '/*') ? $path : Str::finish($path, '/*');
        }, $paths);

        return $paths;
    }

    /**
     * Get & scan all plugins.
     *
     * @return array
     * @throws Exception
     */
    public function scan(): array
    {
        $paths = $this->getScanPaths();

        $plugins = [];

        foreach ($paths as $key => $path) {
            $manifests = $this->getFiles()->glob("{$path}/composer.json");

            is_array($manifests) || $manifests = [];

            foreach ($manifests as $manifest) {
                $info = Json::make($manifest)->getAttributes();
                $name = Arr::get($info, 'name');
                $plugins[$name] = $this->createPlugin(
                    $this->app,
                    $name,
                    dirname($manifest)
                );
            }
        }

        return $plugins;
    }


}