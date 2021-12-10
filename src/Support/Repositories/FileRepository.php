<?php
namespace Yxx\LaravelPlugin\Support\Repositories;

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Traits\Macroable;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Support\PluginModule;

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
     * @return PluginModule
     */
    public function createModule(...$args): PluginModule
    {
        return new PluginModule(...$args);
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
     * @param  string  $key
     * @param  string|null  $default
     * @return mixed
     */
    public function config(string $key, ?string $default = null)
    {
        return $this->config->get('plugins.' . $key, $default);
    }
}