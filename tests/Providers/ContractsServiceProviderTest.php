<?php

namespace Yxx\LaravelPlugin\Tests\Providers;

use Yxx\LaravelPlugin\Contracts\RepositoryInterface;
use Yxx\LaravelPlugin\Support\FileRepository;
use Yxx\LaravelPlugin\Tests\TestCase;

class ContractsServiceProviderTest extends TestCase
{
    public function test_it_binds_repository_interface_with_implementation()
    {
        $this->assertInstanceOf(FileRepository::class, app(RepositoryInterface::class));
    }
}
