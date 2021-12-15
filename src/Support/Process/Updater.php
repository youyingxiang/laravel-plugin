<?php

namespace Yxx\LaravelPlugin\Support\Process;

use Yxx\LaravelPlugin\Support\Plugin;

class Updater extends Runner
{
    /**
     * Update the dependencies for the specified plugin by given the plugin name.
     *
     * @param  string  $name
     */
    public function update(string $name)
    {
        $plugin = $this->repository->findOrFail($name);

        chdir(base_path());

        $this->installRequires($plugin);
        $this->installDevRequires($plugin);
        $this->copyScriptsToMainComposerJson($plugin);
    }

    /**
     * Check if composer should output anything.
     *
     * @return string
     */
    private function isComposerSilenced(): string
    {
        return config('plugins.composer.composer-output') === false ? ' --quiet' : '';
    }

    /**
     * @param  Plugin  $plugin
     */
    private function installRequires(Plugin $plugin)
    {
        $packages = $plugin->getComposerAttr('require', []);

        $concatenatedPackages = '';
        foreach ($packages as $name => $version) {
            $concatenatedPackages .= "\"{$name}:{$version}\" ";
        }

        if (! empty($concatenatedPackages)) {
            $this->run("composer require {$concatenatedPackages}{$this->isComposerSilenced()}");
        }
    }

    /**
     * @param  Plugin  $plugin
     */
    private function installDevRequires(Plugin $plugin)
    {
        $devPackages = $plugin->getComposerAttr('require-dev', []);

        $concatenatedPackages = '';
        foreach ($devPackages as $name => $version) {
            $concatenatedPackages .= "\"{$name}:{$version}\" ";
        }

        if (! empty($concatenatedPackages)) {
            $this->run("composer require --dev {$concatenatedPackages}{$this->isComposerSilenced()}");
        }
    }

    /**
     * @param  Plugin  $plugin
     */
    private function copyScriptsToMainComposerJson(Plugin $plugin)
    {
        $scripts = $plugin->getComposerAttr('scripts', []);

        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        foreach ($scripts as $key => $script) {
            if (array_key_exists($key, $composer['scripts'])) {
                $composer['scripts'][$key] = array_unique(array_merge($composer['scripts'][$key], $script));
                continue;
            }
            $composer['scripts'] = array_merge($composer['scripts'], [$key => $script]);
        }

        file_put_contents(base_path('composer.json'), json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
}
