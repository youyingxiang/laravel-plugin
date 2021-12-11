<?php
namespace Yxx\LaravelPlugin\Abstracts;

use ArrayAccess;
use Exception;
use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Yxx\LaravelPlugin\Support\Json;

abstract class Plugin
{
    use Macroable;

    /**
     * The laravel|lumen application instance.
     *
     * @var Container
     */
    protected Container $app;

    /**
     * The plugin name.
     *
     * @var string
     */
    protected string $name;

    /**
     * @var array of cached Json objects, keyed by filename
     */
    protected array $pluginJson = [];

    /**
     * The plugin path.
     *
     * @var string
     */
    protected string $path;

    /**
     * @var CacheManager
     */
    private CacheManager $cache;

    /**
     * @var Filesystem
     */
    private Filesystem $files;

    /**
     * @var Router
     */
    private Router $router;

    /**
     * Plugin constructor.
     * @param  Container  $app
     * @param  string  $name
     * @param  string  $path
     */
    public function __construct(Container $app, string $name, string $path)
    {
        $this->cache = $app['cache'];
        $this->files = $app['files'];
        $this->router = $app['router'];
        $this->name = $name;
        $this->path = $path;
        $this->app = $app;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get name in lower case.
     *
     * @return string
     */
    public function getLowerName(): string
    {
        return strtolower($this->name);
    }

    /**
     * Get name in studly case.
     *
     * @return string
     */
    public function getStudlyName(): string
    {
        $name = explode('/', $this->name);
        $author = Str::studly($name[0]);
        $plugin = Str::studly($name[1]);

        return $author .'/'. $plugin;
    }

    /**
     * Get json contents from the cache, setting as needed.
     *
     * @param  string|null  $file
     *
     * @return Json
     * @throws Exception
     */
    public function json(?string $file = null): Json
    {
        if ($file === null) {
            $file = 'composer.json';
        }

        return Arr::get($this->pluginJson, $file, function () use ($file) {
            return $this->pluginJson[$file] = new Json($this->getPath() . '/' . $file, $this->files);
        });
    }

    /**
     * Get a specific data from json file by given the key.
     *
     * @param  string  $key
     * @param  null  $default
     *
     * @return mixed
     * @throws Exception
     */
    public function get(string $key, $default = null)
    {
        return $this->json()->get($key, $default);
    }

    /**
     * @param  string  $key
     * @param  null  $default
     * @return array|ArrayAccess|mixed|null
     * @throws Exception
     */
    public function getExtraJuzaweb(string $key, $default = null)
    {
        $extra = $this->get('extra', []);
        if ($laravel = Arr::get($extra, 'juzaweb', [])) {
            return Arr::get($laravel, $key, $default);
        }

        return $default;
    }
}