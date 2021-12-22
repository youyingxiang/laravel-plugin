<?php
namespace Yxx\LaravelPlugin\Tests\Support\Composer;

use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Support\Composer\ComposerRequire;
use Yxx\LaravelPlugin\Support\FileRepository;
use Yxx\LaravelPlugin\Tests\TestCase;

class ComposerRequireTest extends TestCase
{
    protected ComposerRequire $composerRequire;

    protected RepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->composerRequire = new ComposerRequire();
        $this->repository = new FileRepository($this->app);
        $this->artisan('plugin:make', ['name' => ['Test1']]);
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
    }

    public function tearDown(): void
    {
        $this->repository->delete('Test1');
        parent::tearDown();
    }

    public function test_it_can_append_require_plugins()
    {
        $plugin = $this->repository->find('Test1');
        $this->composerRequire->appendRequirePlugins($plugin);
        $this->assertSame($this->composerRequire->getRequirePlugins(),[$plugin]);
    }

    public function test_it_can_gets_remove_requires_by_plugins()
    {
        $plugin = $this->repository->find('Test1');
        $this->composerRequire->appendRequirePlugins($plugin);
        $this->assertSame(
            [
                "require" => [
                    "twilio/sdk" => "^6.28",
                    "tymon/jwt-auth" => "^1.0",
                    "wildbit/swiftmailer-postmark" => "^3.1",
                ],
                "require-dev" => [
                    "laravel/telescope" => "^2.0",
                ]
            ],
            $this->composerRequire->getRequiresByPlugins()
        );
    }
}