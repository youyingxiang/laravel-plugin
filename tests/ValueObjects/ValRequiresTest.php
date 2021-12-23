<?php
namespace Yxx\LaravelPlugin\Tests\ValueObjects;

use Yxx\LaravelPlugin\Tests\TestCase;
use Yxx\LaravelPlugin\ValueObjects\ValRequire;
use Yxx\LaravelPlugin\ValueObjects\ValRequires;

class ValRequiresTest extends TestCase
{
    protected ValRequires $requires;

    public function setUp(): void
    {
        parent::setUp();
        $this->requires = ValRequires::make();
    }

    public function test_it_can_append()
    {
        $vr1 = ValRequire::make("test1\\test1", "1.11");
        $vr2 = ValRequire::make("test2\\test2", "2.22");
        $this->requires->append($vr1);
        $this->assertSame([$vr1], $this->requires->toArray());
        $this->requires->append($vr2);
        $this->assertSame([$vr1, $vr2], $this->requires->toArray());
    }

    public function test_it_can_filter()
    {
        $vr1 = ValRequire::make("test1\\test1", "1.11");
        $vr2 = ValRequire::make("test2\\test2", "2.22");
        $this->requires->append($vr1)->append($vr2);

        $this->assertSame([$vr1, $vr2], $this->requires->toArray());

        $this->assertSame([$vr1], $this->requires->filter(fn(ValRequire $require) => $require->name !== "test2\\test2")->toArray());
    }

    public function test_it_can_merge()
    {
        $vr1 = ValRequire::make("test1\\test1", "1.11");
        $vr2 = ValRequire::make("test2\\test2", "2.22");
        $this->requires->append($vr1)->append($vr2);

        $vr3 = ValRequire::make("test3\\test3", "3.33");
        $vr4 = ValRequire::make("test2\\test2", "2.22");
        $this->requires->merge(ValRequires::make([$vr3, $vr4]));

        $this->assertSame([$vr1, $vr2, $vr3, $vr4], $this->requires->toArray());
    }

    public function test_it_can_unique()
    {
        $vr1 = ValRequire::make("test1\\test1", "1.11");
        $vr2 = ValRequire::make("test2\\test2", "2.22");
        $this->requires->append($vr1)->append($vr2);

        $vr3 = ValRequire::make("test3\\test3", "3.33");
        $vr4 = ValRequire::make("test2\\test2", "2.22");
        $this->requires->merge(ValRequires::make([$vr3, $vr4]));

        $this->assertSame([$vr1, $vr4, $vr3], $this->requires->unique()->toArray());
    }

    public function test_it_can_not_in()
    {
        $vr1 = ValRequire::make("test1\\test1", "1.11");
        $vr2 = ValRequire::make("test2\\test2", "2.22");
        $vrs1 = ValRequires::make([$vr1, $vr2]);

        $vr3 = ValRequire::make("test3\\test3", "3.33");
        $vr4 = ValRequire::make("test2\\test2", "2.22");
        $vrs2 = ValRequires::make([$vr3, $vr4]);

        $this->assertSame([$vr1],  $vrs1->notIn($vrs2)->toArray());
    }

    public function test_it_can_to_val_requires()
    {
        $items = [
            "test1\\test1" =>  "1.11",
            "test2\\test2" =>  "2.22",
        ];

        $this->assertSame("test1\\test1", ValRequires::toValRequires($items)->toArray()[0]->name);
        $this->assertSame("test2\\test2", ValRequires::toValRequires($items)->toArray()[1]->name);
    }

    public function test_it_can_to_string()
    {
        $vr1 = ValRequire::make("test1\\test1", "1.11");
        $vr2 = ValRequire::make("test2\\test2", "2.22");
        $this->requires->append($vr1)->append($vr2);

        $this->assertSame('"test1\test1" "test2\test2" ', $this->requires->__toString());
    }
}