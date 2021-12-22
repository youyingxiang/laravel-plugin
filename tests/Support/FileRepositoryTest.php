<?php

namespace Yxx\LaravelPlugin\Tests\Support;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Contracts\ActivatorInterface;
use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Exceptions\PluginNotFoundException;
use Yxx\LaravelPlugin\Support\Collection;
use Yxx\LaravelPlugin\Support\FileRepository;
use Yxx\LaravelPlugin\Support\Plugin;
use Yxx\LaravelPlugin\Tests\TestCase;

class FileRepositoryTest extends TestCase
{
    /**
     * @var FileRepository
     */
    private FileRepository $repository;

    private Filesystem $finder;

    /**
     * @var ActivatorInterface
     */
    private ActivatorInterface $activator;

    private string $stubsValidPath;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new FileRepository($this->app);
        $this->activator = $this->app[ActivatorInterface::class];
        $this->stubsValidPath = __DIR__.'/../stubs/valid';
        $this->finder = $this->app['files'];
    }

    public function tearDown(): void
    {
        $this->activator->reset();
        $this->app['files']->delete([
            storage_path('app/plugins/plugins.used'),
            base_path('plugins'),
        ]);
        $this->finder->deleteDirectory(base_path('plugins/Test1'));
        $this->finder->deleteDirectory(base_path('plugins/Test2'));
        parent::tearDown();
    }

    public function test_it_adds_location_to_paths()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $paths = $this->repository->getPaths();
        $this->assertCount(1, $paths);
        $this->assertEquals($this->stubsValidPath, $paths[0]);
    }

    public function test_it_returns_a_collection()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $this->assertInstanceOf(Collection::class, $this->repository->toCollection());
        $this->assertInstanceOf(Collection::class, $this->repository->collections());
    }

    public function test_it_returns_all_enabled_plugins()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $this->assertCount(0, $this->repository->getByStatus(true));
        $this->assertCount(0, $this->repository->allEnabled());
    }

    public function test_it_returns_all_disabled_plugins()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $this->assertCount(2, $this->repository->getByStatus(false));
        $this->assertCount(2, $this->repository->allDisabled());
    }

    public function test_it_counts_all_plugins()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $this->assertEquals(2, $this->repository->count());
    }

    public function test_it_finds_a_plugin()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $this->assertInstanceOf(Plugin::class, $this->repository->find('PluginTwo'));
    }

    public function test_it_finds_a_plugin_by_alias()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $this->assertInstanceOf(Plugin::class, $this->repository->findByAlias('plugin_one'));
        $this->assertInstanceOf(Plugin::class, $this->repository->findByAlias('plugin_two'));
    }

    public function test_it_find_or_fail_throws_exception_if_plugin_not_found()
    {
        $this->expectException(PluginNotFoundException::class);
        $this->repository->findOrFail('testplugin');
    }

    public function test_it_gets_the_used_storage_path()
    {
        $path = $this->repository->getUsedStoragePath();

        $this->assertEquals(storage_path('app/plugins/plugins.used'), $path);
    }

    public function test_it_sets_used_plugin()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $this->repository->setUsed('PluginOne');

        $this->assertEquals('PluginOne', $this->repository->getUsedNow());
    }

    public function test_it_returns_laravel_filesystem()
    {
        $this->assertInstanceOf(Filesystem::class, $this->repository->getFiles());
    }

    public function test_it_can_detect_if_plugin_is_active()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $this->repository->enable('PluginOne');

        $this->assertTrue($this->repository->isEnabled('PluginOne'));
    }

    public function test_it_can_detect_if_plugin_is_inactive()
    {
        $this->repository->addLocation($this->stubsValidPath);
        $this->assertTrue($this->repository->isDisabled('PluginOne'));
    }

    public function test_it_can_get_and_set_the_stubs_path()
    {
        $this->repository->setStubPath('some/stub/path');

        $this->assertEquals('some/stub/path', $this->repository->getStubPath());
    }

    public function test_it_gets_the_configured_stubs_path_if_enabled()
    {
        $this->app['config']->set('plugins.stubs.enabled', true);

        $this->assertDirectoryExists($this->repository->getStubPath());
    }

    public function test_it_returns_default_stub_path()
    {
        $this->assertNull($this->repository->getStubPath());
    }

    public function test_it_can_disabled_a_plugin()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $this->repository->disable('PluginOne');

        $this->assertTrue($this->repository->isDisabled('PluginOne'));
    }

    public function test_it_can_enable_a_plugin()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $this->repository->enable('PluginOne');

        $this->assertTrue($this->repository->isEnabled('PluginOne'));
    }

    public function test_it_can_delete_a_module()
    {
        $this->artisan('plugin:make', ['name' => ['Blog']]);

        $this->repository->delete('Blog');

        $this->assertFalse(is_dir(base_path('plugins/Blog')));
    }

    public function test_it_can_find_all_requirements_of_a_plugin()
    {
        $this->repository->addLocation($this->stubsValidPath);

        $requirements = $this->repository->findRequirements('PluginOne');

        $this->assertCount(1, $requirements);
        $this->assertInstanceOf(Plugin::class, $requirements[0]);
    }

    public function test_it_can_register_macros()
    {
        Plugin::macro('registeredMacro', function () {
        });

        $this->assertTrue(Plugin::hasMacro('registeredMacro'));
    }

    public function test_it_does_not_have_unregistered_macros()
    {
        $this->assertFalse(Plugin::hasMacro('unregisteredMacro'));
    }

    public function test_it_calls_macros_on_modules()
    {
        Plugin::macro('getReverseName', function () {
            return strrev($this->getLowerName());
        });

        $this->repository->addLocation($this->stubsValidPath);
        $plugin = $this->repository->find('PluginOne');

        $this->assertEquals('enonigulp', $plugin->getReverseName());
    }

    public function test_it_gets_composer_requires()
    {
        $this->artisan('plugin:make', ['name' => ['Test1']]);
        $this->artisan('plugin:make', ['name' => ['Test2']]);

        $this->repository->find('Test1')->json()->set("composer", [
            "require" => [
                "twilio/sdk" => "^6.28",
                "tymon/jwt-auth" => "^1.0",
                "wildbit/swiftmailer-postmark" => "^3.1",
            ],
            "require-dev" => [
                "laravel/telescope" => "^2.0",
            ],
        ])->save();

        $this->repository->find('Test2')->json()->set("composer", [
            "require" => [
                "wildbit/swiftmailer-postmark" => "^3.1",
                "zircote/swagger-php" => "2.*"
            ],
            "require-dev" => [
                "spatie/laravel-enum" => "1.6.0",
            ],
        ])->save();

        $this->assertSame($this->repository->getComposerRequires(), [
            "twilio/sdk" => "^6.28",
            "tymon/jwt-auth" => "^1.0",
            "wildbit/swiftmailer-postmark" => "^3.1",
            "laravel/telescope" => "^2.0",
            "zircote/swagger-php" => "2.*",
            "spatie/laravel-enum" => "1.6.0",
        ]);

        $this->assertSame($this->repository->getComposerRequires("require"), [
            'twilio/sdk' => '^6.28',
            'tymon/jwt-auth' => '^1.0',
            'wildbit/swiftmailer-postmark' => '^3.1',
            'zircote/swagger-php' => '2.*'
        ]);

        $this->assertSame($this->repository->getComposerRequires("require-dev"), [
            "laravel/telescope" => "^2.0",
            "spatie/laravel-enum" => "1.6.0"
        ]);
    }
}
