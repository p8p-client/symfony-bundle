<?php

/*
 * This file is part of the P8P project.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace P8p\Bundle\DependencyInjection;

use P8p\Bundle\Factory\ClientRegistry;
use P8p\Client\Client;
use P8p\Client\ClientFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

class P8pExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $defaultClient = $config['default_client'];
        $clientServiceIds = [];

        foreach ($config['clients'] as $name => $clientConfig) {
            $clientServiceId = $this->registerClient($container, $name, $clientConfig);
            $clientServiceIds[$name] = $clientServiceId;

            // Create named autowiring alias for variable name injection
            // e.g., Client $productionClient
            $aliasName = $this->createAutowiringAlias($name);
            $container->setAlias(Client::class.' $'.$aliasName, $clientServiceId);
        }

        // Set the default client alias
        if (isset($clientServiceIds[$defaultClient])) {
            $container->setAlias(Client::class, $clientServiceIds[$defaultClient]);
        }

        // Register ClientRegistry
        $this->registerClientRegistry($container, $clientServiceIds, $defaultClient);
    }

    /**
     * Register a Kubernetes client.
     *
     * @param array<string, mixed> $clientConfig
     */
    private function registerClient(ContainerBuilder $container, string $name, array $clientConfig): string
    {
        $dsn = $clientConfig['dsn'];

        // Register ClientFactory
        $factoryServiceId = sprintf('p8p.client_factory.%s', $name);
        $factoryDefinition = new Definition(ClientFactory::class);
        $factoryDefinition->setFactory([new Reference('p8p.client_factory_builder'), 'build']);
        $factoryDefinition->setArguments([
            $dsn,
            new Reference('http_client'),
            new Reference('serializer'),
        ]);
        $container->setDefinition($factoryServiceId, $factoryDefinition);

        // Register Client
        $clientServiceId = sprintf('p8p.client.%s', $name);
        $clientDefinition = new Definition(Client::class);
        $clientDefinition->setFactory([new Reference($factoryServiceId), 'getClient']);
        $clientDefinition->setLazy(true);
        $container->setDefinition($clientServiceId, $clientDefinition);

        return $clientServiceId;
    }

    /**
     * Create an autowiring alias name from a client name.
     * Example: 'production' -> 'productionClient'.
     */
    private function createAutowiringAlias(string $clientName): string
    {
        return lcfirst(str_replace('_', '', ucwords($clientName, '_'))).'Client';
    }

    /**
     * Register the ClientRegistry service.
     *
     * @param array<string, string> $clientServiceIds
     */
    private function registerClientRegistry(
        ContainerBuilder $container,
        array $clientServiceIds,
        string $defaultClient,
    ): void {
        // Create a service locator for lazy loading clients
        $locatorDefinition = new Definition(ServiceLocator::class);
        $locatorDefinition->setArguments([array_map(fn ($id) => new Reference($id), array_values($clientServiceIds))]);
        $locatorDefinition->addTag('container.service_locator');
        $container->setDefinition('p8p.client_locator', $locatorDefinition);

        // Register ClientRegistry
        $registryDefinition = new Definition(ClientRegistry::class);
        $registryDefinition->setArguments([
            new Reference('p8p.client_locator'),
            $clientServiceIds,
            $defaultClient,
        ]);
        $registryDefinition->setPublic(true);
        $container->setDefinition(ClientRegistry::class, $registryDefinition);
    }
}
