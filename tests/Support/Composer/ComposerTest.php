<?php
namespace Yxx\LaravelPlugin\Tests\Support\Composer;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Support\Composer\Composer;
use Yxx\LaravelPlugin\Tests\TestCase;

class ComposerTest extends TestCase
{
    private TestComposer $composer;

    private Filesystem $finder;

    private array $requires;

    public function setUp(): void
    {
        parent::setUp();
        $this->composer = new TestComposer();
        $this->requires = [
            "twilio/sdk" => "^6.28",
            "tymon/jwt-auth" => "^1.0",
            "wildbit/swiftmailer-postmark" => "^3.1",
        ];
        $this->finder = $this->app['files'];
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_it_gets_install_requires_command()
    {
        $this->assertSame('composer require "twilio/sdk:^6.28" "tymon/jwt-auth:^1.0" "wildbit/swiftmailer-postmark:^3.1" ', $this->composer->getInstallRequiresCommand($this->requires));
        $this->assertSame('composer require --dev "twilio/sdk:^6.28" "tymon/jwt-auth:^1.0" "wildbit/swiftmailer-postmark:^3.1" ', $this->composer->getInstallRequiresCommand($this->requires, true));
    }

    public function test_gets_remove_requires_command()
    {
        $this->assertSame('composer remove  "twilio/sdk" "tymon/jwt-auth" "wildbit/swiftmailer-postmark" ', $this->composer->getRemoveRequiresCommand($this->requires));
    }

    public function test_it_can_append_remove_requires()
    {
        $this->composer->appendRemoveRequires($this->requires);

        $this->assertSame($this->requires, $this->composer->getRemoveRequires());

        $this->composer->appendRemoveRequires(["laravel/horizon" => "^3.4"]);

        $this->assertSame(array_merge($this->requires, ["laravel/horizon" => "^3.4"]), $this->composer->getRemoveRequires());
    }


    public function test_it_can_append_requires()
    {
        $this->composer->appendRequires($this->requires);

        $this->assertSame($this->requires, $this->composer->getRequires());

        $this->composer->appendRequires(["laravel/horizon" => "^3.4"]);

        $this->assertSame(array_merge($this->requires, ["laravel/horizon" => "^3.4"]), $this->composer->getRequires());
    }

    public function test_it_can_append_dev_requires()
    {
        $this->composer->appendDevRequires($this->requires);

        $this->assertSame($this->requires, $this->composer->getDevRequires());

        $this->composer->appendDevRequires(["laravel/horizon" => "^3.4"]);

        $this->assertSame(array_merge($this->requires, ["laravel/horizon" => "^3.4"]), $this->composer->getDevRequires());
    }

}

class TestComposer extends Composer
{
    public function handle(): void{}
}