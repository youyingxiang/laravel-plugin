<?php

namespace Yxx\LaravelPlugin\Tests\Support;

use PluginsTest\PluginOne\Providers\PluginOneServiceProvider;
use Yxx\LaravelPlugin\Contracts\ActivatorInterface;
use Yxx\LaravelPlugin\Support\Plugin;
use Yxx\LaravelPlugin\Tests\TestCase;

class PluginTest extends TestCase
{
    private Plugin $plugin;

    private ActivatorInterface $activator;

    public function setUp(): void
    {
        parent::setUp();
        $this->activator = $this->app[ActivatorInterface::class];
        $this->plugin = new Plugin($this->app, 'PluginOne', __DIR__.'/../stubs/valid/PluginOne');
    }

    public function tearDown(): void
    {
        $this->activator->reset();
        $this->app['files']->delete([
            $this->app->bootstrapPath("cache/{$this->plugin->getSnakeName()}_plugin.php"),
        ]);
        parent::tearDown();
    }

    public function test_it_gets_module_name()
    {
        $this->assertEquals('PluginOne', $this->plugin->getName());
    }

    public function test_it_gets_lowercase_module_name()
    {
        $this->assertEquals('pluginone', $this->plugin->getLowerName());
    }

    public function test_it_gets_studly_name()
    {
        $this->assertEquals('PluginOne', $this->plugin->getStudlyName());
    }

    public function test_it_gets_snake_name()
    {
        $this->assertEquals('plugin_one', $this->plugin->getSnakeName());
    }

    public function test_it_gets_plugin_description()
    {
        $this->assertEquals('plugin one test', $this->plugin->getDescription());
    }

    public function test_it_gets_plugin_alias()
    {
        $this->assertEquals('plugin_one', $this->plugin->getAlias());
    }

    public function test_it_gets_plugin_path()
    {
        $this->assertEquals(__DIR__.'/../stubs/valid/PluginOne', $this->plugin->getPath());
    }

    public function test_it_gets_plugin_compress_file_path()
    {
        $this->assertEquals(__DIR__.'/../stubs/valid/PluginOne/.compress/PluginOne.zip', $this->plugin->getCompressFilePath());
    }

    public function test_it_gets_plugin_compress_directory_path()
    {
        $this->assertEquals(__DIR__.'/../stubs/valid/PluginOne/.compress/', $this->plugin->getCompressDirectoryPath());
    }

    public function test_it_gets_required_plugins()
    {
        $this->assertEquals(['plugin_two'], $this->plugin->getRequires());
    }

    public function test_it_loads_plugin_translations()
    {
        $this->app['config']->set('plugins.register.translations', true);
        $this->plugin->boot();
        $this->assertEquals('plugin_one_lang', trans('pluginone::plugin_one.title.plugin_one'));
    }

    public function test_it_reads_plugin_json_files()
    {
        $jsonPlugin = $this->plugin->json();
        $this->assertEquals('PluginOne', $jsonPlugin->get('name'));
    }

    public function test_it_casts_plugin_to_string()
    {
        $this->assertEquals('PluginOne', (string) $this->plugin);
    }

    public function test_it_plugin_status_check()
    {
        $this->assertFalse($this->plugin->isStatus(true));
        $this->assertTrue($this->plugin->isStatus(false));
    }

    public function test_it_checks_plugin_enabled_status()
    {
        $this->assertFalse($this->plugin->isEnabled());
        $this->assertTrue($this->plugin->isDisabled());
    }

    public function test_it_sets_active_status(): void
    {
        $this->plugin->setActive(true);
        $this->assertTrue($this->plugin->isEnabled());
        $this->plugin->setActive(false);
        $this->assertFalse($this->plugin->isEnabled());
    }

    public function test_it_fires_events_when_plugin_is_enabled()
    {
        $this->expectsEvents([
            'plugins.enabling',
            'plugins.enabled'
        ]);

        $this->plugin->enable();
    }

    public function test_it_fires_events_when_plugin_is_disabled()
    {
        $this->expectsEvents([
            'plugins.disabling',
            'plugins.disabled'
        ]);

        $this->plugin->disable();
    }

    public function test_it_has_a_good_providers_manifest_path()
    {
        $this->assertEquals(
            $this->app->bootstrapPath("cache/{$this->plugin->getSnakeName()}_plugin.php"),
            $this->plugin->getCachedServicesPath()
        );
    }

    public function test_it_makes_a_manifest_file_when_providers_are_loaded()
    {
        $cachedServicesPath = $this->plugin->getCachedServicesPath();

        @unlink($cachedServicesPath);
        $this->assertFileNotExists($cachedServicesPath);

        $this->plugin->registerProviders();

        $this->assertFileExists($cachedServicesPath);
        $manifest = require $cachedServicesPath;

        $this->assertEquals([
            'providers' => [
                PluginOneServiceProvider::class,
            ],
            'eager'     => [PluginOneServiceProvider::class],
            'deferred'  => [],
        ], $manifest);
        $this->assertEquals('fooFun', app('foo'));
    }
}
