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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('p8p');

        /* @phpstan-ignore method.notFound */
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('clients')
                    ->info('Kubernetes clients configuration')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('dsn')
                                ->info('Kubernetes DSN (kube://...)')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('default_client')
                    ->info('Name of the default client')
                    ->defaultValue('default')
                ->end()
                ->arrayNode('generator')
                    ->info('CRD generator configuration')
                    ->children()
                        ->scalarNode('namespace')
                            ->info('Base namespace for generated CRD classes')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('path')
                            ->info('Output directory path for generated classes')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('apis')
                            ->info('List of API groups and versions to generate')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('group')
                                        ->info('API group (empty string for core API)')
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('version')
                                        ->info('API version (e.g., v1, v1beta1)')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
