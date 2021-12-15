<?php

if (! function_exists('plugin_path')) {
    function plugin_path(string $name, string $path = ''): string
    {
        $plugin = app('plugins.repository')->find($name);

        return $plugin->getPath().($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}
