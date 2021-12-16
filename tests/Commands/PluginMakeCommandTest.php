<?php
namespace Yxx\LaravelPlugin\Tests\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Yxx\LaravelPlugin\Contracts\ActivatorInterface;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Tests\TestCase;

class PluginMakeCommandTest extends TestCase
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
        $this->assertSame(0, $code);
    }

    public function test_it_generates_plugin_folders()
    {
        $code = $this->artisan('plugin:make', ['name' => ['Blog']]);

        foreach (config('plugins.paths.generator') as $directory) {
            $this->assertDirectoryExists($this->pluginPath . '/' . $directory['path']);
        }
        $this->assertSame(0, $code);
    }

    public function test_it_generates_plugin_files()
    {
        $code = $this->artisan('plugin:make', ['name' => ['Blog']]);

        foreach (config('plugins.stubs.files') as $file) {
            $path = base_path('plugins/Blog') . '/' . $file;
            $this->assertTrue($this->finder->exists($path), "[$file] does not exists");
        }
        $path = base_path('plugins/Blog') . '/plugin.json';
        $this->assertTrue($this->finder->exists($path), '[plugin.json] does not exists');
        $this->assertSame(0, $code);
    }

    public function test_it_generates_plugin_resources()
    {
        $code = $this->artisan('plugin:make', ['name' => ['Blog']]);

        $path = base_path('plugins/Blog') . '/Providers/BlogServiceProvider.php';
        $this->assertTrue($this->finder->exists($path));


        $path = base_path('plugins/Blog') . '/Http/Controllers/BlogController.php';
        $this->assertTrue($this->finder->exists($path));


        $path = base_path('plugins/Blog') . '/Database/Seeders/BlogDatabaseSeeder.php';
        $this->assertTrue($this->finder->exists($path));

        $path = base_path('plugins/Blog') . '/Providers/RouteServiceProvider.php';
        $this->assertTrue($this->finder->exists($path));

        $this->assertSame(0, $code);
    }

    public function test_it_generates_plugin_folder_using_studly_case()
    {
        $code = $this->artisan('plugin:make', ['name' => ['PluginName']]);

        $this->assertTrue($this->finder->exists(base_path('plugins/PluginName')));
        $this->assertSame(0, $code);
    }

    public function test_it_outputs_error_when_plugin_exists()
    {
        $this->artisan('plugin:make', ['name' => ['Blog']]);
        $code = $this->artisan('plugin:make', ['name' => ['Blog']]);

        $expected = 'Plugin [Blog] already exist!
';
        $this->assertEquals($expected, Artisan::output());
        $this->assertSame(E_ERROR, $code);
    }

    public function test_it_still_generates_plugin_if_it_exists_using_force_flag()
    {
        $this->artisan('plugin:make', ['name' => ['Blog']]);
        $code = $this->artisan('plugin:make', ['name' => ['Blog'], '--force' => true]);

        $output = Artisan::output();

        $notExpected = 'Plugin [Blog] already exist!
';
        $this->assertNotEquals($notExpected, $output);
        $this->assertTrue(Str::contains($output, 'Plugin [Blog] created successfully.'));
        $this->assertSame(0, $code);
    }

    public function test_it_can_generate_plugin_with_old_config_format()
    {
        $this->app['config']->set('plugins.paths.generator', [
            'assets' => 'Assets',
            'config' => 'Config',
            'command' => 'Console',
            'event' => 'Events',
            'listener' => 'Listeners',
            'migration' => 'Database/Migrations',
            'factory' => 'Database/factories',
            'model' => 'Entities',
            'repository' => 'Repositories',
            'seeder' => 'Database/Seeders',
            'controller' => 'Http/Controllers',
            'filter' => 'Http/Middleware',
            'request' => 'Http/Requests',
            'provider' => 'Providers',
            'lang' => 'Resources/lang',
            'views' => 'Resources/views',
            'policies' => false,
            'rules' => false,
            'test' => 'Tests',
            'jobs' => 'Jobs',
            'emails' => 'Emails',
            'notifications' => 'Notifications',
            'resource' => false,
        ]);

        $code = $this->artisan('plugin:make', ['name' => ['Blog']]);

        $this->assertDirectoryExists($this->pluginPath . '/Assets');
        $this->assertDirectoryExists($this->pluginPath . '/Emails');
        $this->assertDirectoryNotExists($this->pluginPath . '/Rules');
        $this->assertDirectoryNotExists($this->pluginPath . '/Policies');
        $this->assertSame(0, $code);
    }

    public function test_it_can_ignore_some_folders_to_generate_with_old_format()
    {
        $this->app['config']->set('plugins.paths.generator.assets', false);
        $this->app['config']->set('plugins.paths.generator.emails', false);

        $code = $this->artisan('plugin:make', ['name' => ['Blog']]);

        $this->assertDirectoryNotExists($this->pluginPath . '/Assets');
        $this->assertDirectoryNotExists($this->pluginPath . '/Emails');
        $this->assertSame(0, $code);
    }

    public function test_it_can_ignore_some_folders_to_generate_with_new_format()
    {
        $this->app['config']->set('plugins.paths.generator.assets', ['path' => 'Assets', 'generate' => false]);
        $this->app['config']->set('plugins.paths.generator.emails', ['path' => 'Emails', 'generate' => false]);

        $code = $this->artisan('plugin:make', ['name' => ['Blog']]);

        $this->assertDirectoryNotExists($this->pluginPath . '/Assets');
        $this->assertDirectoryNotExists($this->pluginPath . '/Emails');
        $this->assertSame(0, $code);
    }

    public function test_it_can_ignore_resource_folders_to_generate()
    {
        $this->app['config']->set('plugins.paths.generator.seeder', ['path' => 'Database/Seeders', 'generate' => false]);
        $this->app['config']->set('plugins.paths.generator.provider', ['path' => 'Providers', 'generate' => false]);
        $this->app['config']->set('plugins.paths.generator.controller', ['path' => 'Http/Controllers', 'generate' => false]);

        $code = $this->artisan('plugin:make', ['name' => ['Blog']]);

        $this->assertDirectoryNotExists($this->pluginPath . '/Database/Seeders');
        $this->assertDirectoryNotExists($this->pluginPath . '/Providers');
        $this->assertDirectoryNotExists($this->pluginPath . '/Http/Controllers');
        $this->assertSame(0, $code);
    }


    public function test_it_generates_enabled_plugin()
    {
        $code = $this->artisan('plugin:make', ['name' => ['Blog']]);

        $this->assertTrue($this->repository->isEnabled('Blog'));
        $this->assertSame(0, $code);
    }

    public function test_it_generates_disabled_plugin_with_disabled_flag()
    {
        $code = $this->artisan('plugin:make', ['name' => ['Blog'], '--disabled' => true]);

        $this->assertTrue($this->repository->isDisabled('Blog'));
        $this->assertSame(0, $code);
    }


}