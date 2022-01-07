<?php

namespace Yxx\LaravelPlugin\ValueObjects;

class ValRequire
{
    public string $name;

    public ?string $version;

    public function __construct(string $name, ?string $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public static function make(string $name, ?string $version = ''): ValRequire
    {
        return new static($name, $version);
    }
}
