<?php

/*
 * This file is part of the P8P project.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace P8p\Bundle\Tests\DependencyInjection;

use P8p\Bundle\DependencyInjection\P8pExtension;
use P8p\Bundle\Factory\ClientRegistry;
use P8p\Client\Client;
use P8p\Client\Serializer\Normalizer\SchemaNormalizer;
use P8p\Client\Serializer\Normalizer\WatchEventDenormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class P8pExtensionTest extends TestCase
{
    private P8pExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new P8pExtension();
        $this->container = new ContainerBuilder();

        // Mock required services that would normally be provided by Symfony
        $this->container->register('http_client');
        $this->container->register('serializer');
    }

    public function testLoadWithSingleClient(): void
    {
        $config = [
            'clients' => [
                'default' => [
                    'dsn' => 'kube://in-cluster',
                ],
            ],
        ];

        $this->extension->load([$config], $this->container);

        // Check base services are registered
        $this->assertTrue($this->container->has('p8p.dsn_parser'));
        $this->assertTrue($this->container->has('p8p.client_factory_builder'));
        $this->assertTrue($this->container->has(SchemaNormalizer::class));
        $this->assertTrue($this->container->has(WatchEventDenormalizer::class));

        // Check client services are registered
        $this->assertTrue($this->container->has('p8p.client_factory.default'));
        $this->assertTrue($this->container->has('p8p.client.default'));

        // Check default client alias
        $this->assertTrue($this->container->hasAlias(Client::class));
        $this->assertSame('p8p.client.default', (string) $this->container->getAlias(Client::class));

        // Check named autowiring alias
        $this->assertTrue($this->container->hasAlias(Client::class.' $defaultClient'));
        $this->assertSame('p8p.client.default', (string) $this->container->getAlias(Client::class.' $defaultClient'));

        // Check ClientRegistry is registered
        $this->assertTrue($this->container->has(ClientRegistry::class));
        $registryDef = $this->container->getDefinition(ClientRegistry::class);
        $this->assertTrue($registryDef->isPublic());
    }

    public function testLoadWithMultipleClients(): void
    {
        $config = [
            'clients' => [
                'production' => [
                    'dsn' => 'kube://http?endpoint=https://prod.k8s.local:6443&token=prod-token',
                ],
                'staging' => [
                    'dsn' => 'kube://http?endpoint=https://staging.k8s.local:6443&token=staging-token',
                ],
            ],
            'default_client' => 'production',
        ];

        $this->extension->load([$config], $this->container);

        // Check both clients are registered
        $this->assertTrue($this->container->has('p8p.client.production'));
        $this->assertTrue($this->container->has('p8p.client.staging'));

        // Check default client points to production
        $this->assertSame('p8p.client.production', (string) $this->container->getAlias(Client::class));

        // Check named autowiring aliases
        $this->assertTrue($this->container->hasAlias(Client::class.' $productionClient'));
        $this->assertSame('p8p.client.production', (string) $this->container->getAlias(Client::class.' $productionClient'));

        $this->assertTrue($this->container->hasAlias(Client::class.' $stagingClient'));
        $this->assertSame('p8p.client.staging', (string) $this->container->getAlias(Client::class.' $stagingClient'));
    }
}
