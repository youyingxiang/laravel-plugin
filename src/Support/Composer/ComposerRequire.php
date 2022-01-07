<?php

namespace Yxx\LaravelPlugin\Support\Composer;

use Yxx\LaravelPlugin\Exceptions\ComposerException;
use Yxx\LaravelPlugin\ValueObjects\ValRequires;

class ComposerRequire extends Composer
{
    protected array $pluginRequires = [];
    protected array $pluginDevRequires = [];

    public function appendPluginRequires($pluginName, ValRequires $requires): self
    {
        $this->pluginRequires[$pluginName] = $requires;

        return $this;
    }

    public function appendPluginDevRequires($pluginName, ValRequires $devRequires): self
    {
        $this->pluginDevRequires[$pluginName] = $devRequires;

        return $this;
    }

    public function getPluginRequires(): array
    {
        return $this->pluginRequires;
    }

    public function getPluginDevRequires(): array
    {
        return $this->pluginDevRequires;
    }

    public function getRequiresByPlugins(): ValRequires
    {
        $valRequires = ValRequires::make();

        return array_reduce($this->getPluginRequires(), fn (ValRequires $valRequires, ValRequires $requires) => $valRequires->merge($requires), $valRequires);
    }

    public function getDevRequiresByPlugins(): ValRequires
    {
        $valRequires = ValRequires::make();

        return array_reduce($this->getPluginDevRequires(), fn (ValRequires $valRequires, ValRequires $devRequires) => $valRequires->merge($devRequires), $valRequires);
    }

    public function beforeRun(): void
    {
        if ($this->getPluginRequires()) {
            $this->appendRequires($this->getRequiresByPlugins());
        }

        if ($this->getPluginDevRequires()) {
            $this->appendDevRequires($this->getDevRequiresByPlugins());
        }
    }

    public function afterRun(): void
    {
        $failedrequires = $this->filterExistRequires($this->getRequires()->merge($this->getDevRequires()));

        if ($failedrequires->notEmpty()) {
            throw new ComposerException("Package {$failedrequires}require failed");
        }
    }
}
