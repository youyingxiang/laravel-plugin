<?php

namespace Yxx\LaravelPlugin\Tests\Support\Composer;

use Illuminate\Filesystem\Filesystem;
use Yxx\LaravelPlugin\Support\Composer\Composer;
use Yxx\LaravelPlugin\Tests\TestCase;
use Yxx\LaravelPlugin\ValueObjects\ValRequire;
use Yxx\LaravelPlugin\ValueObjects\ValRequires;

class ComposerTest extends TestCase
{
    private TestComposer $composer;

    private Filesystem $finder;

    private ValRequires $requires;

    public function setUp(): void
    {
        parent::setUp();
        $this->composer = new TestComposer();
        $vr1 = ValRequire::make('twilio/sdk', '^6.28');
        $vr2 = ValRequire::make('tymon/jwt-auth', '^1.0');
        $vr3 = ValRequire::make('wildbit/swiftmailer-postmark', '^3.1');
        $this->requires = ValRequires::make([$vr1, $vr2, $vr3]);
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

        $this->assertSame($this->requires->toArray(), $this->composer->getRemoveRequires()->toArray());

        $vl = ValRequire::make('laravel/horizon', '^3.4');
        $removeRequires = ValRequires::make([$vl]);
        $this->composer->appendRemoveRequires($removeRequires);

        $this->assertSame($this->requires->append($vl)->toArray(), $this->composer->getRemoveRequires()->toArray());
    }

    public function test_it_can_append_requires()
    {
        $this->composer->appendRequires($this->requires);

        $this->assertSame($this->requires->toArray(), $this->composer->getRequires()->toArray());

        $vl = ValRequire::make('laravel/horizon', '^3.4');

        $requires = ValRequires::make()->append($vl);

        $this->composer->appendRequires($requires);

        $this->assertSame($this->requires->append($vl)->toArray(), $this->composer->getRequires()->toArray());
    }

    public function test_it_can_append_dev_requires()
    {
        $this->composer->appendDevRequires($this->requires);

        $this->assertSame($this->requires->toArray(), $this->composer->getDevRequires()->toArray());

        $vl = ValRequire::make('laravel/horizon', '^3.4');

        $devRequires = ValRequires::make()->append($vl);

        $this->composer->appendDevRequires($devRequires);

        $this->assertSame($this->requires->append($vl)->toArray(), $this->composer->getDevRequires()->toArray());
    }
}

class TestComposer extends Composer
{
    public function beforeRun(): void
    {
        // TODO: Implement beforeRun() method.
    }

    public function afterRun(): void
    {
        // TODO: Implement afterRun() method.
    }
}
