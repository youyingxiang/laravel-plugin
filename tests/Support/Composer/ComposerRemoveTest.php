<?php
namespace Yxx\LaravelPlugin\Tests\Support\Composer;

use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Exceptions\ComposerException;
use Yxx\LaravelPlugin\Support\Composer\ComposerRemove;
use Yxx\LaravelPlugin\Support\FileRepository;
use Yxx\LaravelPlugin\Tests\TestCase;
use Yxx\LaravelPlugin\ValueObjects\ValRequires;

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
                "twilio/sdk" => "^6.28",
                "tymon/jwt-auth" => "^1.0",
            ],
            "require-dev" => [
                "laravel/sanctum" => "^2.11",
            ],
        ])->save();
    }

    public function tearDown(): void
    {
        $this->repository->delete('Test1');
        $this->repository->delete('Test2');
        parent::tearDown();
    }

    public function test_it_can_append_remove_plugins()
    {
        $plugin1 = $this->repository->find('Test1');
        $this->composerRemove->appendRemovePluginRequires($plugin1->getName(), $plugin1->getAllComposerRequires());

        $plugin2 = $this->repository->find('Test2');
        $this->composerRemove->appendRemovePluginRequires($plugin2->getName(), $plugin2->getAllComposerRequires());

        $this->assertTrue(ValRequires::toValRequires([
            "twilio/sdk" => "^6.28",
            "tymon/jwt-auth" => "^1.0",
            "wildbit/swiftmailer-postmark" => "^3.1",
            "laravel/telescope" => "^2.0",
        ])->equals($this->composerRemove->getRemovePluginRequires()['Test1']));

        $this->assertTrue(ValRequires::toValRequires([
            "twilio/sdk" => "^6.28",
            "tymon/jwt-auth" => "^1.0",
            "laravel/sanctum" => "^2.11",
        ])->equals($this->composerRemove->getRemovePluginRequires()['Test2']));
    }

    public function test_it_can_append_remove_requires_not_in_plugins()
    {
        $this->expectException(ComposerException::class);
        $plugin1 = $this->repository->find('Test1');
        $this->composerRemove->appendRemovePluginRequires($plugin1->getName(), ValRequires::toValRequires(['test/zz' => '1.11']));

    }

    public function test_it_can_gets_remove_requires_by_plugins()
    {
        $plugin1 = $this->repository->find('Test1');
        $this->composerRemove->appendRemovePluginRequires($plugin1->getName(), $plugin1->getAllComposerRequires());

        $this->assertTrue(ValRequires::toValRequires([
            "wildbit/swiftmailer-postmark" => "^3.1",
            "laravel/telescope" => "^2.0"
        ])->equals($this->composerRemove->getRemoveRequiresByPlugins()));
    }
}