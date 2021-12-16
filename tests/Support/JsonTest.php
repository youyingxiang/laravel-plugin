<?php

namespace Yxx\LaravelPlugin\Tests\Support;

use Yxx\LaravelPlugin\Exceptions\InvalidJsonException;
use Yxx\LaravelPlugin\Support\Json;
use Yxx\LaravelPlugin\Tests\TestCase;

class JsonTest extends TestCase
{
    /**
     * @var Json
     */
    private Json $json;

    public function setUp(): void
    {
        parent::setUp();
        $path = __DIR__.'/../stubs/valid/plugin.json';
        $this->json = new Json($path, $this->app['files']);
    }

    public function test_it_gets_the_file_path()
    {
        $path = __DIR__.'/../stubs/valid/plugin.json';
        $this->assertEquals($path, $this->json->getPath());
    }

    public function test_it_throws_an_exception_with_invalid_json()
    {
        $path = __DIR__.'/../stubs/InvalidJsonPlugin/plugin.json';

        $this->expectException(InvalidJsonException::class);
        $this->expectExceptionMessage('Error processing file: '.$path.'. Error: Syntax error');

        new Json($path, $this->app['files']);
    }

    public function test_it_gets_attributes_from_json_file()
    {
        $this->assertEquals('Test', $this->json->get('name'));
        $this->assertEquals('test', $this->json->get('alias'));
        $this->assertEquals('Test demo plugin', $this->json->get('description'));
        $this->assertEquals('0.1', $this->json->get('version'));
        $this->assertEquals(['test', 'stub', 'plugin'], $this->json->get('keywords'));
        $this->assertEquals(1, $this->json->get('active'));
        $this->assertEquals(1, $this->json->get('order'));
    }

    public function test_it_reads_attributes_from_magic_get_method()
    {
        $this->assertEquals('Test', $this->json->name);
        $this->assertEquals('test', $this->json->alias);
        $this->assertEquals('Test demo plugin', $this->json->description);
        $this->assertEquals('0.1', $this->json->version);
        $this->assertEquals(['test', 'stub', 'plugin'], $this->json->keywords);
        $this->assertEquals(1, $this->json->active);
        $this->assertEquals(1, $this->json->order);
    }

    public function test_it_makes_json_class()
    {
        $path = __DIR__.'/../stubs/valid/plugin.json';
        $json = Json::make($path, $this->app['files']);

        $this->assertInstanceOf(Json::class, $json);
    }

    public function test_it_sets_a_path()
    {
        $path = __DIR__.'/../stubs/valid/plugin.json';
        $this->assertEquals($path, $this->json->getPath());

        $this->json->setPath('some/path.json');
        $this->assertEquals('some/path.json', $this->json->getPath());
    }

    public function test_it_decodes_json()
    {
        $expected = '{
    "name": "Test",
    "alias": "test",
    "description": "Test demo plugin",
    "version": "0.1",
    "keywords": [
        "test",
        "stub",
        "plugin"
    ],
    "active": 1,
    "order": 1,
    "providers": [
        "PluginsTest\\\Test\\\Providers\\\TestServiceProvider",
        "PluginsTest\\\Test\\\Providers\\\TestServiceProvider"
    ],
    "aliases": [],
    "files": []
}';

        $this->assertEquals(str_replace("\r\n", "\n", $expected), str_replace("\r\n", "\n", $this->json->toJsonPretty()));
    }

    public function test_it_sets_a_key_value()
    {
        $this->json->set('key', 'value');

        $this->assertEquals('value', $this->json->get('key'));
    }

    public function it_can_be_casted_to_string()
    {
        $expected = '{
    "name": "Test",
    "alias": "test",
    "description": "Test demo plugin",
    "version": "0.1",
    "keywords": [
        "test",
        "stub",
        "plugin"
    ],
    "active": 1,
    "order": 1,
    "providers": [
        "PluginsTest\\\Test\\\Providers\\\TestServiceProvider",
        "PluginsTest\\\Test\\\Providers\\\TestServiceProvider"
    ],
    "aliases": [],
    "files": []
}';
        $this->assertEquals(str_replace("\r\n", "\n", $expected), str_replace("\r\n", "\n", $this->json));
    }
}
