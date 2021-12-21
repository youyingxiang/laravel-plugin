<?php
namespace Yxx\LaravelPlugin\Support\Composer;

use Yxx\LaravelPlugin\Contracts\RunableInterface;
use Yxx\LaravelPlugin\Support\Plugin;

class PluginJsonRequire implements RunableInterface
{
    /**
     * @var Plugin
     */
    protected Plugin $plugin;

    /**
     * CompressPlugin constructor.
     *
     * @param  Plugin  $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function __invoke()
    {
        chdir(base_path());

        $this->installRequires();
        $this->installDevRequires();
    }


    private function installRequires(): void
    {
        $packages = $this->plugin->getComposerAttr('require') ?? [];

        $concatenatedPackages = '';

        foreach ($packages as $name => $version) {
            $concatenatedPackages .= "\"{$name}:{$version}\" ";
        }

        if (! empty($concatenatedPackages)) {
            $this->run("composer require {$concatenatedPackages}");
        }
    }

    private function installDevRequires()
    {
        $devPackages = $this->plugin->getComposerAttr('require-dev') ?? [];

        $concatenatedPackages = '';
        foreach ($devPackages as $name => $version) {
            $concatenatedPackages .= "\"{$name}:{$version}\" ";
        }

        if (! empty($concatenatedPackages)) {
            $this->run("composer require --dev {$concatenatedPackages}");
        }
    }

    /**
     * @param  Plugin  $plugin
     * @return PluginJsonRequire
     */
    public static function make(Plugin $plugin):PluginJsonRequire
    {
        return new self($plugin);
    }


    /**
     * @param  string  $command
     */
    public function run(string $command): void
    {
        passthru($command);
    }
}