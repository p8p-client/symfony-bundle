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

use P8p\Bundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private Configuration $configuration;
    private Processor $processor;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testGetConfigTreeBuilder(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();

        $this->assertSame('p8p', $treeBuilder->buildTree()->getName());
    }

    public function testMinimalValidConfiguration(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'default' => [
                        'dsn' => 'kube://in-cluster',
                    ],
                ],
            ],
        ];

        $processedConfig = $this->processor->processConfiguration($this->configuration, [$config['p8p']]);

        $this->assertArrayHasKey('clients', $processedConfig);
        $this->assertArrayHasKey('default_client', $processedConfig);
        $this->assertSame('default', $processedConfig['default_client']);
        $this->assertSame('kube://in-cluster', $processedConfig['clients']['default']['dsn']);
    }

    public function testMultipleClientsConfiguration(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'production' => [
                        'dsn' => 'kube://http?endpoint=https://prod.k8s.local:6443&token=prod-token',
                    ],
                    'staging' => [
                        'dsn' => 'kube://http?endpoint=https://staging.k8s.local:6443&token=staging-token',
                    ],
                ],
                'default_client' => 'production',
            ],
        ];

        $processedConfig = $this->processor->processConfiguration($this->configuration, [$config['p8p']]);

        $this->assertCount(2, $processedConfig['clients']);
        $this->assertArrayHasKey('production', $processedConfig['clients']);
        $this->assertArrayHasKey('staging', $processedConfig['clients']);
        $this->assertSame('production', $processedConfig['default_client']);
    }

    public function testCustomDefaultClient(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'prod' => [
                        'dsn' => 'kube://in-cluster',
                    ],
                    'dev' => [
                        'dsn' => 'kube://http?endpoint=http://localhost:8001',
                    ],
                ],
                'default_client' => 'dev',
            ],
        ];

        $processedConfig = $this->processor->processConfiguration($this->configuration, [$config['p8p']]);

        $this->assertSame('dev', $processedConfig['default_client']);
    }

    public function testMissingClientsThrowsException(): void
    {
        $config = [
            'p8p' => [],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('clients');

        $this->processor->processConfiguration($this->configuration, [$config['p8p']]);
    }

    public function testEmptyClientsArrayThrowsException(): void
    {
        $config = [
            'p8p' => [
                'clients' => [],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('at least 1 element');

        $this->processor->processConfiguration($this->configuration, [$config['p8p']]);
    }

    public function testMissingDsnThrowsException(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'default' => [],
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('dsn');

        $this->processor->processConfiguration($this->configuration, [$config['p8p']]);
    }

    public function testEmptyDsnThrowsException(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'default' => [
                        'dsn' => '',
                    ],
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('cannot contain an empty value');

        $this->processor->processConfiguration($this->configuration, [$config['p8p']]);
    }

    public function testClientNameAsKey(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'my_custom_name' => [
                        'dsn' => 'kube://in-cluster',
                    ],
                ],
            ],
        ];

        $processedConfig = $this->processor->processConfiguration($this->configuration, [$config['p8p']]);

        $this->assertArrayHasKey('my_custom_name', $processedConfig['clients']);
        $this->assertArrayNotHasKey('name', $processedConfig['clients']['my_custom_name']);
    }

    public function testValidGeneratorConfiguration(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'default' => [
                        'dsn' => 'kube://in-cluster',
                    ],
                ],
                'generator' => [
                    'namespace' => 'App\\K8s\\CRD',
                    'path' => '/path/to/output',
                    'apis' => [
                        [
                            'group' => 'cert-manager.io',
                            'version' => 'v1',
                        ],
                        [
                            'group' => '',
                            'version' => 'v1',
                        ],
                    ],
                ],
            ],
        ];

        $processedConfig = $this->processor->processConfiguration($this->configuration, [$config['p8p']]);

        $this->assertArrayHasKey('generator', $processedConfig);
        $this->assertSame('App\\K8s\\CRD', $processedConfig['generator']['namespace']);
        $this->assertSame('/path/to/output', $processedConfig['generator']['path']);
        $this->assertCount(2, $processedConfig['generator']['apis']);
        $this->assertSame('cert-manager.io', $processedConfig['generator']['apis'][0]['group']);
        $this->assertSame('v1', $processedConfig['generator']['apis'][0]['version']);
    }

    public function testGeneratorMissingNamespaceThrowsException(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'default' => [
                        'dsn' => 'kube://in-cluster',
                    ],
                ],
                'generator' => [
                    'path' => '/path/to/output',
                    'apis' => [
                        [
                            'group' => 'cert-manager.io',
                            'version' => 'v1',
                        ],
                    ],
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('namespace');

        $this->processor->processConfiguration($this->configuration, [$config['p8p']]);
    }

    public function testGeneratorMissingPathThrowsException(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'default' => [
                        'dsn' => 'kube://in-cluster',
                    ],
                ],
                'generator' => [
                    'namespace' => 'App\\K8s\\CRD',
                    'apis' => [
                        [
                            'group' => 'cert-manager.io',
                            'version' => 'v1',
                        ],
                    ],
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('path');

        $this->processor->processConfiguration($this->configuration, [$config['p8p']]);
    }

    public function testGeneratorMissingApisThrowsException(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'default' => [
                        'dsn' => 'kube://in-cluster',
                    ],
                ],
                'generator' => [
                    'namespace' => 'App\\K8s\\CRD',
                    'path' => '/path/to/output',
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('apis');

        $this->processor->processConfiguration($this->configuration, [$config['p8p']]);
    }

    public function testGeneratorEmptyApisThrowsException(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'default' => [
                        'dsn' => 'kube://in-cluster',
                    ],
                ],
                'generator' => [
                    'namespace' => 'App\\K8s\\CRD',
                    'path' => '/path/to/output',
                    'apis' => [],
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('at least 1 element');

        $this->processor->processConfiguration($this->configuration, [$config['p8p']]);
    }

    public function testGeneratorApiMissingGroupThrowsException(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'default' => [
                        'dsn' => 'kube://in-cluster',
                    ],
                ],
                'generator' => [
                    'namespace' => 'App\\K8s\\CRD',
                    'path' => '/path/to/output',
                    'apis' => [
                        [
                            'version' => 'v1',
                        ],
                    ],
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('group');

        $this->processor->processConfiguration($this->configuration, [$config['p8p']]);
    }

    public function testGeneratorApiMissingVersionThrowsException(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'default' => [
                        'dsn' => 'kube://in-cluster',
                    ],
                ],
                'generator' => [
                    'namespace' => 'App\\K8s\\CRD',
                    'path' => '/path/to/output',
                    'apis' => [
                        [
                            'group' => 'cert-manager.io',
                        ],
                    ],
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('version');

        $this->processor->processConfiguration($this->configuration, [$config['p8p']]);
    }

    public function testGeneratorApiEmptyVersionThrowsException(): void
    {
        $config = [
            'p8p' => [
                'clients' => [
                    'default' => [
                        'dsn' => 'kube://in-cluster',
                    ],
                ],
                'generator' => [
                    'namespace' => 'App\\K8s\\CRD',
                    'path' => '/path/to/output',
                    'apis' => [
                        [
                            'group' => 'cert-manager.io',
                            'version' => '',
                        ],
                    ],
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('cannot contain an empty value');

        $this->processor->processConfiguration($this->configuration, [$config['p8p']]);
    }
}
