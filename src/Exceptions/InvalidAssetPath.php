<?php

namespace Yxx\LaravelPlugin\Exceptions;

class InvalidAssetPath extends \Exception
{
    public static function missingPluginName($asset): InvalidAssetPath
    {
        return new static("Plugin name was not specified in asset [$asset].");
    }
}
