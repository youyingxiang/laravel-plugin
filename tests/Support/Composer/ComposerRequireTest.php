<?php

namespace Yxx\LaravelPlugin\Tests\Support\Composer;

use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Support\Composer\ComposerRequire;
use Yxx\LaravelPlugin\Support\FileRepository;
use Yxx\LaravelPlugin\Tests\TestCase;
use Yxx\LaravelPlugin\ValueObjects\ValRequires;

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
        $this->artisan('plugin:make', ['name' => ['Test2']]);
        $this->repository->find('Test1')->json()->set('composer', [
            'require' => [
                'twilio/sdk' => '^6.28',
                'tymon/jwt-auth' => '^1.0',
                'wildbit/swiftmailer-postmark' => '^3.1',
            ],
            'require-dev' => [
                'laravel/telescope' => '^2.0',
            ],
        ])->save();
        $this->repository->find('Test2')->json()->set('composer', [
            'require' => [
                'twilio/sdk' => '^6.28',
                'tymon/jwt-auth' => '^1.0',
            ],
            'require-dev' => [
                'laravel/sanctum' => '^2.11',
            ],
        ])->save();
    }

    public function tearDown(): void
    {
        $this->repository->delete('Test1');
        $this->repository->delete('Test2');
        parent::tearDown();
    }

    public function test_it_can_append_require_plugins()
    {
        $plugin = $this->repository->find('Test1');
        $this->composerRequire->appendPluginRequires('Test1', $plugin->getComposerAttr('require'));
        $this->assertTrue($plugin->getComposerAttr('require')->equals($this->composerRequire->getPluginRequires()['Test1']));
    }

    public function test_it_can_append_dev_require_plugins()
    {
        $plugin = $this->repository->find('Test1');
        $this->composerRequire->appendPluginDevRequires('Test1', $plugin->getComposerAttr('require-dev'));
        $this->assertTrue($plugin->getComposerAttr('require-dev')->equals($this->composerRequire->getPluginDevRequires()['Test1']));
    }

    public function test_it_can_gets_remove_requires_by_plugins()
    {
        $plugin1 = $this->repository->find('Test1');
        $plugin2 = $this->repository->find('Test2');
        $this->composerRequire->appendPluginRequires('Test1', $plugin1->getComposerAttr('require'))->appendPluginRequires('Test2', $plugin2->getComposerAttr('require'));

        $this->assertTrue($this->composerRequire->getRequiresByPlugins()->unique()->equals(ValRequires::toValRequires([
            'twilio/sdk' => '^6.28',
            'tymon/jwt-auth' => '^1.0',
            'wildbit/swiftmailer-postmark' => '^3.1',
        ])));

        $this->composerRequire->appendPluginDevRequires('Test1', $plugin1->getComposerAttr('require-dev'))->appendPluginDevRequires('Test2', $plugin2->getComposerAttr('require-dev'));

        $this->assertTrue($this->composerRequire->getDevRequiresByPlugins()->unique()->equals(ValRequires::toValRequires([
            'laravel/telescope' => '^2.0',
            'laravel/sanctum' => '^2.11',
        ])));
    }
}
