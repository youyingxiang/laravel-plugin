<?php

namespace Yxx\LaravelPlugin\Tests\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Yxx\LaravelPlugin\Contracts\ActivatorInterface;
use Yxx\LaravelPlugin\Tests\TestCase;

class PluginDeleteCommandTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private Filesystem $finder;
    /**
     * @var ActivatorInterface
     */
    private ActivatorInterface $activator;

    public function setUp(): void
    {
        parent::setUp();
        $this->finder = $this->app['files'];
        $this->activator = $this->app[ActivatorInterface::class];
    }

    public function test_it_can_delete_a_plugin_from_disk(): void
    {
        Event::fake();
        $this->artisan('plugin:make', ['name' => ['WrongPlugin']]);
        $this->assertDirectoryExists(base_path('plugins/WrongPlugin'));

        $code = $this->artisan('plugin:delete', ['plugin' => 'WrongPlugin']);
        $this->assertDirectoryNotExists(base_path('plugins/WrongPlugin'));
        $this->assertSame(0, $code);
        Event::assertDispatched("plugins.deleting");
        Event::assertDispatched("plugins.deleted");
    }
}
