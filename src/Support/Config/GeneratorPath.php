<?php
namespace Yxx\LaravelPlugin\Support\Config;

class GeneratorPath
{
    private string $path;
    private bool $generate;
    private string $namespace;

    public function __construct($config)
    {
        if (is_array($config)) {
            $this->path = $config['path'];
            $this->generate = $config['generate'];
            $this->namespace = $config['namespace'] ?? $this->convertPathToNamespace($config['path']);

            return;
        }
        $this->path = $config;
        $this->generate = (bool) $config;
        $this->namespace = $config;
    }

    public function getPath():string
    {
        return $this->path;
    }

    public function generate(): bool
    {
        return $this->generate;
    }

    public function getNamespace():string
    {
        return $this->namespace;
    }

    private function convertPathToNamespace($path):string
    {
        return str_replace('/', '\\', $path);
    }
}