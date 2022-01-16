<?php

namespace Yxx\LaravelPlugin\Support;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Translation\Translator;
use Yxx\LaravelPlugin\Contracts\ActivatorInterface;
use Yxx\LaravelPlugin\ValueObjects\ValRequires;

class Plugin
{
    use Macroable;

    /**
     * The laravel|lumen application instance.
     *
     * @var ApplicationContract;
     */
    protected ApplicationContract $app;

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
     * @var array of cached Json objects, keyed by filename
     */
    protected array $pluginJson = [];

    /**
     * @var CacheManager
     */
    private CacheManager $cache;

    /**
     * @var Filesystem
     */
    private Filesystem $files;

    /**
     * @var Translator
     */
    private Translator $translator;

    /**
     * @var ActivatorInterface
     */
    private ActivatorInterface $activator;

    /**
     * Plugin constructor.
     *
     * @param  ApplicationContract  $app
     * @param  string  $name
     * @param  string  $path
     */
    public function __construct(ApplicationContract $app, string $name, string $path)
    {
        $this->name = $name;
        $this->path = $path;
        $this->cache = $app['cache'];
        $this->files = $app['files'];
        $this->translator = $app['translator'];
        $this->activator = $app[ActivatorInterface::class];
        $this->app = $app;
    }

    /**
     * Register the Plugin.
     */
    public function register(): void
    {
        $this->registerAliases();

        $this->registerProviders();

        $this->fireEvent('register');
    }

    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        if (config('plugins.register.translations', true) === true) {
            $this->registerTranslation();
        }

        $this->fireEvent('boot');
    }

    /**
     * @return string
     */
    public function getCachedServicesPath(): string
    {
        // This checks if we are running on a Laravel Vapor managed instance
        // and sets the path to a writable one (services path is not on a writable storage in Vapor).
        if (! is_null(env('VAPOR_MAINTENANCE_MODE', null))) {
            return Str::replaceLast('config.php', $this->getSnakeName().'_plugin.php', $this->app->getCachedConfigPath());
        }

        return Str::replaceLast('services.php', $this->getSnakeName().'_plugin.php', $this->app->getCachedServicesPath());
    }

    public function registerProviders(): void
    {
        (new ProviderRepository($this->app, new Filesystem(), $this->getCachedServicesPath()))
            ->load($this->get('providers', []));
    }

    public function registerAliases(): void
    {
        $loader = AliasLoader::getInstance();
        foreach ($this->get('aliases', []) as $aliasName => $aliasClass) {
            $loader->alias($aliasName, $aliasClass);
        }
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
        return Str::studly($this->name);
    }

    /**
     * Get name in snake case.
     *
     * @return string
     */
    public function getSnakeName(): string
    {
        return Str::snake($this->name);
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->get('description');
    }

    /**
     * Get alias.
     *
     * @return string
     */
    public function getAlias(): string
    {
        return $this->get('alias');
    }

    /**
     * Get priority.
     *
     * @return string
     */
    public function getPriority(): string
    {
        return $this->get('priority');
    }

    /**
     * Get plugin requirements.
     *
     * @return array
     */
    public function getRequires(): array
    {
        return $this->get('requires');
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
     * Set path.
     *
     * @param  string  $path
     * @return Plugin
     */
    public function setPath(string $path): Plugin
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Register plugin's translation.
     *
     * @return void
     */
    protected function registerTranslation(): void
    {
        $lowerName = $this->getLowerName();

        $langPath = $this->getPath().'/Resources/lang';

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $lowerName);
        }
    }

    /**
     * Get json contents from the cache, setting as needed.
     *
     * @param  string|null  $file
     * @return Json
     */
    public function json(?string $file = null): Json
    {
        if ($file === null) {
            $file = 'plugin.json';
        }

        return Arr::get($this->pluginJson, $file, function () use ($file) {
            return $this->pluginJson[$file] = new Json($this->getPath().'/'.$file, $this->files);
        });
    }

    /**
     * Get a specific data from json file by given the key.
     *
     * @param  string  $key
     * @param  null  $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->json()->get($key, $default);
    }

    /**
     * @param $key
     * @param  array  $default
     * @return ValRequires
     */
    public function getComposerAttr(string $key, $default = []): ValRequires
    {
        return ValRequires::toValRequires(data_get($this->json()->get('composer'), $key, $default));
    }

    /**
     * @return ValRequires
     */
    public function getAllComposerRequires(): ValRequires
    {
        $composer = $this->json()->get('composer');

        return ValRequires::toValRequires(data_get($composer, 'require', []))->merge(ValRequires::toValRequires(data_get($composer, 'require-dev', [])));
    }

    /**
     * @return Filesystem
     */
    public function getFiles(): Filesystem
    {
        return $this->files;
    }

    /**
     * Register the Plugin event.
     *
     * @param  string  $event
     */
    protected function fireEvent($event): void
    {
        $this->app['events']->dispatch('plugins.'.$event, [$this]);
    }

    public function fireInstalledEvent(): void
    {
        $this->fireEvent('installed');
    }

    /**
     * Handle call __toString.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getStudlyName();
    }

    /**
     * Determine whether the given status same with the current plugin status.
     *
     * @param  bool  $status
     * @return bool
     */
    public function isStatus(bool $status): bool
    {
        return $this->activator->hasStatus($this, $status);
    }

    /**
     * Determine whether the current plugin activated.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->activator->hasStatus($this, true);
    }

    /**
     *  Determine whether the current plugin not disabled.
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return ! $this->isEnabled();
    }

    /**
     * Set active state for current plugin.
     *
     * @param  bool  $active
     * @return void
     */
    public function setActive(bool $active): void
    {
        $this->activator->setActive($this, $active);
    }

    public function getCompressFilePath(): string
    {
        return $this->getPath().'/.compress/'.$this->getName().'.zip';
    }

    public function getCompressDirectoryPath(): string
    {
        return $this->getPath().'/.compress/';
    }

    /**
     * Disable the current plugin.
     */
    public function disable(): void
    {
        $this->fireEvent('disabling');

        $this->activator->disable($this);
        $this->flushCache();

        $this->fireEvent('disabled');
    }

    /**
     * Enable the current plugin.
     */
    public function enable(): void
    {
        $this->fireEvent('enabling');

        $this->activator->enable($this);
        $this->flushCache();

        $this->fireEvent('enabled');
    }

    /**
     * Delete the current plugin.
     *
     * @return bool
     */
    public function delete(): bool
    {
        $this->fireEvent('deleting');

        $this->activator->delete($this);

        $res = $this->json()->getFilesystem()->deleteDirectory($this->getPath());

        $this->fireEvent('deleted');

        return $res;
    }

    /**
     * Get extra path.
     *
     * @param  string  $path
     * @return string
     */
    public function getExtraPath(string $path): string
    {
        return $this->getPath().'/'.$path;
    }

    /**
     * Check if can load files of plugin on boot method.
     */
    protected function isLoadFilesOnBoot(): bool
    {
        return false;
    }

    private function flushCache(): void
    {
        if (config('plugins.cache.enabled')) {
            $this->cache->store()->flush();
        }
    }

    /**
     * Register a translation file namespace.
     *
     * @param  string  $path
     * @param  string  $namespace
     * @return void
     */
    private function loadTranslationsFrom(string $path, string $namespace): void
    {
        $this->translator->addNamespace($namespace, $path);
    }
}
