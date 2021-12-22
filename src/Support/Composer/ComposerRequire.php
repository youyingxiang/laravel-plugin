<?php
namespace Yxx\LaravelPlugin\Support\Composer;

use Yxx\LaravelPlugin\Support\Plugin;

class ComposerRequire extends Composer
{
    protected array $requirePlugins = [];

    public function appendRequirePlugins(Plugin $plugin): self
    {
        $this->requirePlugins[] = $plugin;
        return $this;
    }

    public function getRequirePlugins(): array
    {
        return $this->requirePlugins;
    }

    public function getRequiresByPlugins():array
    {
        /** @var Plugin $plugin */
        $requires = [];

        foreach ($this->getRequirePlugins() as $plugin) {
            $requires['require'] = array_merge($requires['require'] ?? [], $plugin->getComposerAttr('require') ?? []);
            $requires['require-dev'] = array_merge($requires['require-dev'] ?? [], $plugin->getComposerAttr('require-dev') ?? []);
        }
        return $requires;
    }

    public function handle(): void
    {
        if ($this->getRequirePlugins()) {
            $requires = $this->getRequiresByPlugins();
            $this->appendRequires(data_get($requires, "require"));
            $this->appendDevRequires(data_get($requires, "require-dev"));
        }
        $this->run();
    }
}