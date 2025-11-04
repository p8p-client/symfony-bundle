<?php

/*
 * This file is part of the P8P project.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace P8p\Bundle\Tests\Factory;

use P8p\Bundle\Factory\ClientRegistry;
use P8p\Client\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ClientRegistryTest extends TestCase
{
    private ContainerInterface&MockObject $locator;

    protected function setUp(): void
    {
        $this->locator = $this->createMock(ContainerInterface::class);
    }

    public function testGetExistingClient(): void
    {
        $mockClient = $this->createMock(Client::class);

        $this->locator
            ->expects($this->once())
            ->method('get')
            ->with('p8p.client.production')
            ->willReturn($mockClient);

        $registry = new ClientRegistry(
            $this->locator,
            ['production' => 'p8p.client.production'],
            'production'
        );

        $client = $registry->get('production');

        $this->assertSame($mockClient, $client);
    }

    public function testGetNonExistingClientThrowsException(): void
    {
        $registry = new ClientRegistry(
            $this->locator,
            ['production' => 'p8p.client.production'],
            'production'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Client "staging" does not exist. Available clients: production');

        $registry->get('staging');
    }

    public function testGetDefault(): void
    {
        $mockClient = $this->createMock(Client::class);

        $this->locator
            ->expects($this->once())
            ->method('get')
            ->with('p8p.client.production')
            ->willReturn($mockClient);

        $registry = new ClientRegistry(
            $this->locator,
            ['production' => 'p8p.client.production', 'staging' => 'p8p.client.staging'],
            'production'
        );

        $client = $registry->getDefault();

        $this->assertSame($mockClient, $client);
    }

    public function testHasReturnsTrueForExistingClient(): void
    {
        $registry = new ClientRegistry(
            $this->locator,
            ['production' => 'p8p.client.production', 'staging' => 'p8p.client.staging'],
            'production'
        );

        $this->assertTrue($registry->has('production'));
        $this->assertTrue($registry->has('staging'));
    }

    public function testHasReturnsFalseForNonExistingClient(): void
    {
        $registry = new ClientRegistry(
            $this->locator,
            ['production' => 'p8p.client.production'],
            'production'
        );

        $this->assertFalse($registry->has('staging'));
        $this->assertFalse($registry->has('nonexistent'));
    }

    public function testGetClientNames(): void
    {
        $registry = new ClientRegistry(
            $this->locator,
            ['production' => 'p8p.client.production', 'staging' => 'p8p.client.staging', 'dev' => 'p8p.client.dev'],
            'production'
        );

        $names = $registry->getClientNames();

        $this->assertSame(['production', 'staging', 'dev'], $names);
    }
}
