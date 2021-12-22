<?php
namespace Yxx\LaravelPlugin\Support\Composer;

use Yxx\LaravelPlugin\Support\Plugin;

class ComposerRemove extends Composer
{
    protected array $removePlugins = [];

    public function appendRemovePlugins(Plugin $plugin): self
    {
        $this->removePlugins[] = $plugin;
        return $this;
    }

    /**
     * @return array
     */
    public function getRemovePlugins():array
    {
        return $this->removePlugins;
    }

    /**
     * @return array
     */
    public function getRemoveRequiresByPlugins():array
    {
        return collect($this->getRemovePlugins())->mapWithKeys(
            fn(Plugin $plugin) => array_merge(
                $plugin->getComposerAttr('require') ?? [],
                $plugin->getComposerAttr('require-dev') ?? []
            )
        )->toArray();
    }

    public function handle(): void
    {
        if ($this->getRemovePlugins()) {
            $this->appendRemoveRequires($this->getRemoveRequiresByPlugins());
        }
        $this->run();
    }
}