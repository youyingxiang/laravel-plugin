<?php

namespace Yxx\LaravelPlugin\Tests\Activators;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Activators\FileActivator;
use Yxx\LaravelPlugin\Support\Plugin;
use Yxx\LaravelPlugin\Tests\TestCase;

class FileActivatorTest extends TestCase
{
    private Plugin $plugin;

    private Filesystem $finder;

    private FileActivator $activator;

    public function setUp(): void
    {
        parent::setUp();
        $this->plugin = new Plugin($this->app, 'PluginOne', __DIR__.'/../stubs/valid/PluginOne');
        $this->finder = $this->app['files'];
        $this->activator = new FileActivator($this->app);
    }

    public function tearDown(): void
    {
        $this->activator->reset();
        parent::tearDown();
    }

    public function test_it_creates_valid_json_file_after_enabling()
    {
        $this->activator->enable($this->plugin);
        $pluginsStatuses = json_decode($this->finder->get($this->activator->getStatusesFilePath()), true);
        $this->assertTrue(data_get($pluginsStatuses, 'PluginOne'));

        $this->activator->setActive($this->plugin, true);
        $pluginsStatuses = json_decode($this->finder->get($this->activator->getStatusesFilePath()), true);
        $this->assertTrue(data_get($pluginsStatuses, 'PluginOne'));
    }

    public function test_it_creates_valid_json_file_after_disabling()
    {
        $this->activator->disable($this->plugin);
        $pluginsStatuses = json_decode($this->finder->get($this->activator->getStatusesFilePath()), true);
        $this->assertFalse(data_get($pluginsStatuses, 'PluginOne'));

        $this->activator->setActive($this->plugin, false);
        $pluginsStatuses = json_decode($this->finder->get($this->activator->getStatusesFilePath()), true);
        $this->assertFalse(data_get($pluginsStatuses, 'PluginOne'));
    }

    public function test_it_can_check_plugin_enabled_status()
    {
        $this->activator->enable($this->plugin);
        $this->assertTrue($this->activator->hasStatus($this->plugin, true));

        $this->activator->setActive($this->plugin, true);
        $this->assertTrue($this->activator->hasStatus($this->plugin, true));
    }

    public function test_it_can_check_plugin_disabled_status()
    {
        $this->activator->disable($this->plugin);
        $this->assertTrue($this->activator->hasStatus($this->plugin, false));

        $this->activator->setActive($this->plugin, false);
        $this->assertTrue($this->activator->hasStatus($this->plugin, false));
    }

    public function test_it_can_check_status_of_plugin_that_hasnt_been_enabled_or_disabled()
    {
        $this->assertTrue($this->activator->hasStatus($this->plugin, false));
    }
}
