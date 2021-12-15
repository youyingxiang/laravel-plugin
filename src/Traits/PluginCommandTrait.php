<?php

namespace Yxx\LaravelPlugin\Traits;

use Yxx\LaravelPlugin\Support\Plugin;

trait PluginCommandTrait
{
    protected Plugin $plugin;

    /**
     * Get the plugin name.
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return $this->getPlugin()->getStudlyName();
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        if (isset($this->plugin) && $this->plugin instanceof Plugin) {
            return $this->plugin;
        }
        $plugin = $this->argument('plugin') ?: app('plugins.repository')->getUsedNow();

        $this->plugin = app('plugins.repository')->findOrFail($plugin);

        return $this->plugin;
    }
}
