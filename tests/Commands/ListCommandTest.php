<?php
namespace Yxx\LaravelPlugin\Tests\Commands;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Tests\TestCase;

class ListCommandTest extends TestCase
{

    /**
     * @var Filesystem
     */
    private Filesystem $finder;
    /**
     * @var string
     */
    private string  $pluginPath;

    public function setUp(): void
    {
        parent::setUp();
        $this->pluginPath = base_path('plugins/Blog');
        $this->finder = $this->app['files'];
        $this->artisan('plugin:make', ['name' => ['Blog']]);
    }

    public function tearDown(): void
    {
        $this->app[RepositoryInterface::class]->delete('Blog');
        parent::tearDown();
    }

    public function test_it_can_list_plugins()
    {
        $code = $this->artisan('plugin:list');

        // We just want to make sure nothing throws an exception inside the list command
        $this->assertTrue(true);
        $this->assertSame(0, $code);
    }
}