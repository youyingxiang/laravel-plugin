<?php
namespace Yxx\LaravelPlugin\Tests\Support;

use Yxx\LaravelPlugin\Support\Collection;
use Yxx\LaravelPlugin\Support\Plugin;
use Yxx\LaravelPlugin\Tests\TestCase;

class CollectionTest extends TestCase
{
    public function test_to_array_sets_path_attribute()
    {
        $pluginOnePath = __DIR__ . '/../stubs/valid/PluginOne';
        $pluginTwoPath = __DIR__ . '/../stubs/valid/PluginTwo';
        $plugins = [
            new Plugin($this->app, 'PluginOne', $pluginOnePath),
            new Plugin($this->app, 'PluginTwo', $pluginTwoPath),
        ];
        $collection = new Collection($plugins);
        $collectionArray = $collection->toArray();

        $this->assertArrayHasKey('path', $collectionArray[0]);
        $this->assertEquals($pluginOnePath, $collectionArray[0]['path']);
        $this->assertArrayHasKey('path', $collectionArray[1]);
        $this->assertEquals($pluginTwoPath, $collectionArray[1]['path']);
    }

    /** @test */
    public function getItemsReturnsTheCollectionItems()
    {
        $pluginOnePath = __DIR__ . '/../stubs/valid/PluginOne';
        $pluginTwoPath = __DIR__ . '/../stubs/valid/PluginTwo';

        $plugins = [
            new Plugin($this->app, 'PluginOne', $pluginOnePath),
            new Plugin($this->app, 'PluginTwo', $pluginTwoPath),
        ];
        $collection = new Collection($plugins);
        $items = $collection->getItems();

        $this->assertCount(2, $items);
        $this->assertInstanceOf(Plugin::class, $items[0]);
    }
}