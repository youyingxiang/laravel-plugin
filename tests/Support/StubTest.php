<?php
namespace Yxx\LaravelPlugin\Tests\Support;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Support\Stub;
use Yxx\LaravelPlugin\Tests\TestCase;

class StubTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private Filesystem $finder;

    public function setUp(): void
    {
        parent::setUp();
        $this->finder = $this->app['files'];
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->finder->delete([
            base_path('my-seeder.php'),
            base_path('stub-override-not-exists.php'),
            base_path('stub-override-exists.php')
        ]);
    }

    public function test_it_initialises_a_stub_instance()
    {
        $stub = new Stub('/plugin.stub', [
            'NAME' => 'Name',
        ]);

        $this->assertEquals(['NAME' => 'Name', ], $stub->getReplaces());
        $this->assertStringContainsString('/stubs/plugin.stub', $stub->getPath());
    }

    public function test_it_sets_new_replaces_array()
    {
        $stub = new Stub('/plugin.stub', [
            'NAME' => 'Name',
        ]);

        $stub->replace(['PLUGIN' => 'MyPlugin', ]);
        $this->assertEquals(['PLUGIN' => 'MyPlugin', ], $stub->getReplaces());
    }

    public function test_it_stores_stub_to_specific_path()
    {
        $stub = (new Stub('/seeder.stub', [
            'NAME'      => "TestSeeder",
            'NAMESPACE' => "Plugins\Test\Database\Seeders",
        ]));

        $stub->saveTo(base_path(), 'my-seeder.php');

        $this->assertTrue($this->finder->exists(base_path('my-seeder.php')));
    }

    public function test_it_sets_new_path()
    {
        $stub = new Stub('/plugin.stub', [
            'NAME' => 'Name',
        ]);

        $stub->setPath('/new-path/');

        $this->assertStringContainsString('/stubs/new-path/', $stub->getPath());
    }

    public function test_use_default_stub_if_override_not_exists()
    {
        $stub = new Stub('/seeder.stub', [
            'NAME'      => "TestSeeder",
            'NAMESPACE' => "Plugins\Test\Database\Seeders",
        ]);

        $stub->saveTo(base_path(), 'stub-override-not-exists.php');

        $this->assertTrue($this->finder->exists(base_path('stub-override-not-exists.php')));
    }

    public function test_use_override_stub_if_exists()
    {
        $stub = new Stub('/seeder.stub', [
            'NAME'      => "TestSeeder",
            'NAMESPACE' => "Plugins\Test\Database\Seeders",
        ]);

        $stub->saveTo(base_path(), 'stub-override-exists.php');

        $this->assertTrue($this->finder->exists(base_path('stub-override-exists.php')));
        $content = $this->finder->get(base_path('stub-override-exists.php'));
        $this->assertStringContainsString('Plugins\\Test\\Database\\Seeders;', $content);
        $this->assertStringContainsString('TestSeeder', $content);
    }


}