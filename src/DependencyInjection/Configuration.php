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
            ->end()
        ;

        return $treeBuilder;
    }
}
