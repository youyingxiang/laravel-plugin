<?php
namespace Yxx\LaravelPlugin\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Yxx\LaravelPlugin\Support\Json;
use Yxx\LaravelPlugin\Traits\PluginCommandTrait;

class ComposerRequireCommand extends Command
{
    use PluginCommandTrait;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'plugin:composer-require';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the plugin composer package.';


    public function handle(): void
    {
        $package = $this->argument('package');
        $require = $this->option('dev') ? "require-dev" : "require";
        $dev = $this->option('dev') ? "--dev" : null;
        $v = $this->option('v');

        $pluginJson = $this->getPlugin()->json();

        $requires = array_merge($this->getPlugin()->getComposerAttr("require") ?? [], $this->getPlugin()->getComposerAttr("require-dev") ?? []);

        if (data_get($requires, $package)) {
            $this->warn("Package already exists in `{$this->getPluginName()}`");
            return;
        }

        passthru("composer require {$package} $v $dev");

        $version = data_get(Json::make("composer.json")->setIsCache(false)->get($require), $package);

        if ($version) {
            $composer = $pluginJson->get('composer') ?? [];
            $composer[$require][$package] = $version;
            $pluginJson->set('composer', $composer)->save();
            $this->info("Package $package $version generated successfully.");
        } else {
            $this->error("Package $package $version generated failed.");
        }

    }


    protected function getArguments(): array
    {
        return [
            ['package', InputArgument::REQUIRED, 'The name of the composer package name.'],
            ['plugin', InputArgument::OPTIONAL, 'The name of plugins will be used.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['dev', null, InputOption::VALUE_NONE, 'Only the composer package of the dev environment exists.'],
            ['v', null, InputOption::VALUE_OPTIONAL, 'The version number of the composer package.'],
        ];
    }
}