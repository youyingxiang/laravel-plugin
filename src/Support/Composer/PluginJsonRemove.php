<?php
namespace Yxx\LaravelPlugin\Support\Composer;

use Illuminate\Support\Arr;
use Yxx\LaravelPlugin\Contracts\RunableInterface;
use Yxx\LaravelPlugin\Support\Plugin;

class PluginJsonRemove implements RunableInterface
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

        $deletePluginRequires = array_merge($this->plugin->getComposerAttr('require') ?? [], $this->plugin->getComposerAttr('require-dev') ?? []);

        $otherPluginRequires = [];
        /** @var Plugin $plugin */
        foreach (app('plugins.repository')->scan() as $plugin) {
            if ($plugin->getName() !== $this->plugin->getName()) {
                $otherPluginRequires[] = array_merge($plugin->getComposerAttr('require') ?? [], $plugin->getComposerAttr('require-dev') ?? []);
            }
        }

        $concatenatedPackages = '';
        foreach ($deletePluginRequires as $name => $version) {
            if (! key_exists($name, Arr::collapse($otherPluginRequires))) {
                $concatenatedPackages .= "\"{$name}\" ";
            }
        }

        if (! empty($concatenatedPackages)) {
            $this->run("composer remove  {$concatenatedPackages}");
        }

    }

    /**
     * @param  Plugin  $plugin
     * @return PluginJsonRemove
     */
    public static function make(Plugin $plugin):PluginJsonRemove
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