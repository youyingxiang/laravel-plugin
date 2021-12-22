<?php
namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Yxx\LaravelPlugin\Support\Json;
use Yxx\LaravelPlugin\Support\Plugin;
use Yxx\LaravelPlugin\Traits\PluginCommandTrait;

class ComposerRemoveCommand extends Command
{
    use PluginCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'plugin:composer-remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the plugin composer package.';


    public function handle():void
    {
        $package = $this->argument('package');

        $executeComposer = true;

        /** @var Plugin $plugin */
        foreach (app('plugins.repository')->scan() as $plugin) {
            $composer = $plugin->get('composer');
            $pluginRequires = array_merge(data_get($composer, 'require') ?? [], data_get($composer, 'require-dev') ?? []);
            if ($plugin->getName() !== $this->getPluginName() && data_get($pluginRequires, $package)) {
                $executeComposer = false;
            }
        }
        $pluginJson = $this->getPlugin()->json();

        $executeComposer && passthru("composer remove {$package}");

        $requires = array_merge(
            Json::make("composer.json")->setIsCache(false)->get('require'),
            Json::make("composer.json")->setIsCache(false)->get('require-dev')
        );

        if (! data_get($requires, $package) || ! $executeComposer) {
            $composer = $pluginJson->get('composer') ?? [];
            Arr::forget($composer, "require.$package");
            Arr::forget($composer, "require-dev.$package");
            $pluginJson->set('composer', $composer)->save();
            $this->info("Package $package remove successfully.");
        } else {
            $this->error("Package $package failed.");
        }

    }

    protected function getArguments(): array
    {
        return [
            ['package', InputArgument::REQUIRED, 'The name of the composer package name.'],
            ['plugin', InputArgument::OPTIONAL, 'The name of plugins will be used.'],
        ];
    }
}