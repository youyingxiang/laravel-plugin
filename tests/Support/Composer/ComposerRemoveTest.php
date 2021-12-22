<?php
namespace Yxx\LaravelPlugin\Tests\Support\Composer;

use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Support\Composer\ComposerRemove;
use Yxx\LaravelPlugin\Support\FileRepository;
use Yxx\LaravelPlugin\Tests\TestCase;

class ComposerRemoveTest extends TestCase
{
    protected ComposerRemove $composerRemove;

    protected RepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->composerRemove = new ComposerRemove();
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

    public function test_it_can_append_remove_plugins()
    {
        $plugin = $this->repository->find('Test1');
        $this->composerRemove->appendRemovePlugins($plugin);
        $this->assertSame($this->composerRemove->getRemovePlugins(),[$plugin]);
    }

    public function test_it_can_gets_remove_requires_by_plugins()
    {
        $plugin = $this->repository->find('Test1');
        $this->composerRemove->appendRemovePlugins($plugin);
        $this->assertSame(
            [
                "twilio/sdk" => "^6.28",
                "tymon/jwt-auth" => "^1.0",
                "wildbit/swiftmailer-postmark" => "^3.1",
                "laravel/telescope" => "^2.0",
            ],
            $this->composerRemove->getRemoveRequiresByPlugins()
        );
    }
}