<?php
namespace Yxx\LaravelPlugin\Contracts;

interface RepositoryInterface
{
    /**
     * Get all plugins.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Get scanned plugins paths.
     *
     * @return array
     */
    public function getScanPaths(): array;

    /**
     * Scan & get all available plugins.
     *
     * @return array
     */
    public function scan(): array;
    /**
     * Get all ordered plugins.
     * @param string $direction
     * @return array
     */
    public function getOrdered($direction = 'asc'): array;

    /**
     * @param  string  $key
     * @param  string|null  $default
     * @return mixed
     */
    public function config(string $key, ?string $default = null);

    /**
     * @param  string  $plugin
     * @return string
     */
    public function getPluginPath(string $plugin): string;
}