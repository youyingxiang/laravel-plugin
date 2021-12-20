<?php
namespace Yxx\LaravelPlugin\Tests\Commands;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Tests\TestCase;

class LocalInstallCommandTest extends TestCase
{
    private Filesystem $filesystem;

    private string $localPath;

    private RepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->localPath = __DIR__."/../stubs/valid/";
        $this->filesystem = $this->app['files'];
        $this->repository = $this->app[RepositoryInterface::class];
    }

    public function tearDown(): void
    {
        optional($this->repository->find("PluginOne"))->delete();
        optional($this->repository->find("Plugin3"))->delete();
        parent::tearDown();
    }

    public function test_it_can_local_install_by_directory()
    {
        $this->artisan('plugin:local-install', ['path' => $this->localPath. "PluginOne"]);
        $this->assertDirectoryExists($this->repository->find("PluginOne")->getPath());
        $this->assertTrue($this->repository->find("PluginOne")->isEnabled());
    }

    public function test_it_can_local_install_by_zip()
    {
        $this->artisan('plugin:local-install', ['path' => $this->localPath. "Plugin3.zip"]);
        $this->assertDirectoryExists($this->repository->find("Plugin3")->getPath());
        $this->assertTrue($this->repository->find("Plugin3")->isEnabled());
    }
}