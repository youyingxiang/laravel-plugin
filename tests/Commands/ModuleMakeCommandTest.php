<?php

namespace Yxx\LaravelPlugin\Tests\Commands;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Contracts\ActivatorInterface;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Tests\TestCase;

class ModuleMakeCommandTest extends TestCase
{
    private Filesystem $finder;

    private string $pluginPath;

    private ActivatorInterface $activator;

    private RepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->pluginPath = base_path('plugins/Blog');
        $this->finder = $this->app['files'];
        $this->repository = $this->app[RepositoryInterface::class];
        $this->activator = $this->app[ActivatorInterface::class];
    }

    public function tearDown(): void
    {
        $this->finder->deleteDirectory($this->pluginPath);
        if ($this->finder->isDirectory(base_path('plugins/PluginName'))) {
            $this->finder->deleteDirectory(base_path('plugins/PluginName'));
        }
        $this->activator->reset();
        parent::tearDown();
    }

    public function test_it_generates_plugin()
    {
        $code = $this->artisan('plugin:make', ['name' => ['Blog']]);
        $this->assertDirectoryExists($this->pluginPath);
    }
}
