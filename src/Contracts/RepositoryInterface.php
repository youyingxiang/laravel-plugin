<?php
namespace Yxx\LaravelPlugin\Contracts;

interface RepositoryInterface
{
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
}