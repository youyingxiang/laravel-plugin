<?php

namespace Yxx\LaravelPlugin\Support;

use ArrayAccess;
use Illuminate\Support\Arr;

class Config
{
    /**
     * Get the given configuration value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return array|ArrayAccess|mixed
     */
    public static function get($key, $default = null)
    {
        return Arr::get(static::load(), $key, $default);
    }

    /**
     * Store the given configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public static function set($key, $value): void
    {
        $config = static::load();

        Arr::set($config, $key, $value);

        file_put_contents(static::path(), json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Load the entire configuration array.
     *
     * @return array
     */
    public static function load(): array
    {
        if (! is_dir(dirname(static::path()))) {
            mkdir(dirname(static::path()), 0755, true);
        }

        if (file_exists(static::path())) {
            return json_decode(file_get_contents(static::path()), true);
        }

        return [];
    }

    /**
     * Get the path to the configuration file.
     *
     * @return string
     */
    protected static function path(): string
    {
        return ($_SERVER['HOME'] ?? $_SERVER['USERPROFILE']).'/.laravel-plugin/config.json';
    }
}
