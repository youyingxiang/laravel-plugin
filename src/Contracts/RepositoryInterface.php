<?php

namespace Yxx\LaravelPlugin\Contracts;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Exceptions\PluginNotFoundException;
use Yxx\LaravelPlugin\Support\Collection;
use Yxx\LaravelPlugin\Support\Plugin;

interface RepositoryInterface
{
    /**
     * Get all plugins.
     *
     * @return mixed
     */
    public function all();

    /**
     * Get cached plugins.
     *
     * @return array
     */
    public function getCached(): array;

    /**
     * Scan & get all available plugins.
     *
     * @return array
     */
    public function scan();

    /**
     * Get plugin as plugins collection instance.
     *
     * @return Collection
     */
    public function toCollection(): Collection;

    /**
     * Get scanned paths.
     *
     * @return array
     */
    public function getScanPaths(): array;

    /**
     * Get list of enabled plugins.
     *
     * @return mixed
     */
    public function allEnabled();

    /**
     * Get list of disabled plugins.
     *
     * @return mixed
     */
    public function allDisabled();

    /**
     * Get count from all plugins.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get all ordered plugins.
     *
     * @param  string  $direction
     * @return mixed
     */
    public function getOrdered($direction = 'asc');

    /**
     * Get plugins by the given status.
     *
     * @param  bool  $status
     * @return array
     */
    public function getByStatus(bool $status): array;

    /**
     * Find a specific plugin.
     *
     * @param $name
     * @return Plugin|null
     */
    public function find(string $name): ?Plugin;

    /**
     * Find all plugins that are required by a plugin. If the plugin cannot be found, throw an exception.
     *
     * @param $name
     * @return array
     *
     * @throws PluginNotFoundException
     */
    public function findRequirements($name): array;

    /**
     * Find a specific plugin. If there return that, otherwise throw exception.
     *
     * @param $name
     * @return Plugin
     */
    public function findOrFail(string $name): Plugin;

    /**
     * @param  string  $pluginName
     * @return string
     */
    public function getPluginPath(string $pluginName): string;

    /**
     * @return Filesystem
     */
    public function getFiles(): Filesystem;

    /**
     * Get a specific config data from a configuration file.
     *
     * @param  string  $key
     * @param  string|null  $default
     * @return mixed
     */
    public function config(string $key, $default = null);

    /**
     * Get a plugin path.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Find a specific plugin by its alias.
     *
     * @param  string  $alias
     * @return Plugin|null
     */
    public function findByAlias(string $alias): ?Plugin;

    /**
     * Boot the plugins.
     */
    public function boot(): void;

    /**
     * Register the plugins.
     */
    public function register(): void;

    /**
     * Get asset path for a specific plugin.
     *
     * @param  string  $name
     * @return string
     */
    public function assetPath(string $name): string;

    /**
     * Delete a specific plugin.
     *
     * @param  string  $name
     * @return bool
     *
     * @throws PluginNotFoundException
     */
    public function delete(string $name): bool;

    /**
     * Determine whether the given plugin is activated.
     *
     * @param  string  $name
     * @return bool
     *
     * @throws PluginNotFoundException
     */
    public function isEnabled(string $name): bool;

    /**
     * Determine whether the given plugin is not activated.
     *
     * @param  string  $name
     * @return bool
     *
     * @throws PluginNotFoundException
     */
    public function isDisabled(string $name): bool;
}
