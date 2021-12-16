<?php
namespace Yxx\LaravelPlugin\Tests\Commands;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Tests\TestCase;

class ProviderMakeCommandTest extends TestCase
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

    public function test_it_generates_a_service_provider()
    {
        $code = $this->artisan('plugin:make-provider', ['name' => 'MyBlogServiceProvider', 'plugin' => 'Blog']);

        $this->assertFileExists($this->pluginPath . '/Providers/MyBlogServiceProvider.php');
        $this->assertSame(0, $code);
    }


}