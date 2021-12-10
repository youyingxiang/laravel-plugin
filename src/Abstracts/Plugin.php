<?php
namespace Yxx\LaravelPlugin\Abstracts;

use Illuminate\Cache\CacheManager;
use Illuminate\Console\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Illuminate\Support\Traits\Macroable;

abstract class Plugin
{
    use Macroable;

    /**
     * The laravel|lumen application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * The plugin name.
     *
     * @var string
     */
    protected string $name;

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
     * @param  Application  $app
     * @param  string  $name
     * @param  string  $path
     */
    public function __construct(Application $app, string $name, string $path)
    {
        $this->cache = $app['cache'];
        $this->files = $app['files'];
        $this->router = $app['router'];
        $this->name = $name;
        $this->path = $path;
        $this->app = $app;
    }
}