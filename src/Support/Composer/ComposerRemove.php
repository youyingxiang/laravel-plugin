<?php

namespace Yxx\LaravelPlugin\Support\Composer;

use Yxx\LaravelPlugin\Exceptions\ComposerException;
use Yxx\LaravelPlugin\ValueObjects\ValRequires;

class ComposerRemove extends Composer
{
    protected array $removePluginRequires = [];

    public function appendRemovePluginRequires($pluginName, ValRequires $removeRequires): self
    {
        $currentPlugin = $this->repository->findOrFail($pluginName);
        $notRemoveRequires = $removeRequires->notIn($currentPlugin->getAllComposerRequires());

        if ($notRemoveRequires->notEmpty()) {
            throw new ComposerException("Package $notRemoveRequires is not in the plugin $pluginName.");
        }

        $this->removePluginRequires[$currentPlugin->getName()] = $removeRequires;

        return $this;
    }

    /**
     * @return array
     */
    public function getRemovePluginRequires(): array
    {
        return $this->removePluginRequires;
    }

    /**
     * @return ValRequires
     */
    public function getRemoveRequiresByPlugins(): ValRequires
    {
        $pluginNames = array_keys($this->getRemovePluginRequires());

        $valRequires = ValRequires::make();
        $removePluginRequires = array_reduce($this->getRemovePluginRequires(), function (ValRequires $valRequires, ValRequires $removePluginRequires) {
            return $valRequires->merge($removePluginRequires);
        }, $valRequires);

        if ($relyOtherPluginRemoveRequires = $this->repository->getExceptPluginNameComposerRequires($pluginNames)) {
            return $removePluginRequires->notIn($relyOtherPluginRemoveRequires);
        }

        return $removePluginRequires;
    }

    public function beforeRun(): void
    {
        if ($this->getRemovePluginRequires()) {
            $this->appendRemoveRequires($this->getRemoveRequiresByPlugins());
        }
    }

    public function afterRun(): void
    {
        $failedrequires = $this->getRemoveRequires()->in($this->getExistRequires())->unique();

        if ($failedrequires->notEmpty()) {
            throw new ComposerException("Package {$failedrequires} remove failed");
        }
    }
}
