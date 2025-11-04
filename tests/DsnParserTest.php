<?php

/*
 * This file is part of the P8P project.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace P8p\Bundle\Tests;

use P8p\Bundle\Exception\InvalidDsnException;
use P8p\Bundle\Factory\DsnParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DsnParserTest extends TestCase
{
    private DsnParser $parser;

    protected function setUp(): void
    {
        $this->parser = new DsnParser();
    }

    /**
     * @param array<string, string> $expectedParameters
     */
    #[DataProvider('validHttpDsnProvider')]
    public function testParseValidHttpDsn(string $dsn, array $expectedParameters): void
    {
        $parsed = $this->parser->parse($dsn);

        $this->assertSame('http', $parsed->provider);
        $this->assertSame($expectedParameters, $parsed->parameters);
    }

    /**
     * @return array<string, array{string, array<string, string>}>
     */
    public static function validHttpDsnProvider(): array
    {
        return [
            'simple http' => [
                'kube://http?endpoint=http://127.0.0.1:8001',
                ['endpoint' => 'http://127.0.0.1:8001'],
            ],
            'https with token' => [
                'kube://http?endpoint=https://api.k8s.local:6443&token=my-token',
                ['endpoint' => 'https://api.k8s.local:6443', 'token' => 'my-token'],
            ],
            'with all parameters' => [
                'kube://http?endpoint=https://api.k8s.local:6443&token=/run/secrets/token&ca=/run/secrets/ca.crt&cert=/run/secrets/cert.crt&key=/run/secrets/key.pem',
                [
                    'endpoint' => 'https://api.k8s.local:6443',
                    'token' => '/run/secrets/token',
                    'ca' => '/run/secrets/ca.crt',
                    'cert' => '/run/secrets/cert.crt',
                    'key' => '/run/secrets/key.pem',
                ],
            ],
        ];
    }

    public function testParseValidInClusterDsn(): void
    {
        $parsed = $this->parser->parse('kube://in-cluster');

        $this->assertSame('in-cluster', $parsed->provider);
        $this->assertEmpty($parsed->parameters);
    }

    /**
     * @param array<string, string> $expectedParameters
     */
    #[DataProvider('validKubeconfigDsnProvider')]
    public function testParseValidKubeconfigDsn(string $dsn, array $expectedParameters): void
    {
        $parsed = $this->parser->parse($dsn);

        $this->assertSame('kubeconfig', $parsed->provider);
        $this->assertSame($expectedParameters, $parsed->parameters);
    }

    /**
     * @return array<string, array{string, array<string, string>}>
     */
    public static function validKubeconfigDsnProvider(): array
    {
        return [
            'kubeconfig with path only' => [
                'kube://kubeconfig?path=/path/to/kubeconfig',
                ['path' => '/path/to/kubeconfig'],
            ],
            'kubeconfig with context' => [
                'kube://kubeconfig?path=/path/to/kubeconfig&context=production',
                ['path' => '/path/to/kubeconfig', 'context' => 'production'],
            ],
        ];
    }

    #[DataProvider('invalidDsnProvider')]
    public function testParseInvalidDsn(string $dsn, string $expectedMessage): void
    {
        $this->expectException(InvalidDsnException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->parser->parse($dsn);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidDsnProvider(): array
    {
        return [
            'invalid scheme' => [
                'mysql://localhost',
                'Invalid DSN scheme',
            ],
            'missing provider' => [
                'kube://',
                'Missing provider in DSN',
            ],
            'unknown provider' => [
                'kube://unknown',
                'Unknown provider: "unknown"',
            ],
            'http without endpoint' => [
                'kube://http',
                'Missing required parameter "endpoint"',
            ],
            'http with invalid endpoint' => [
                'kube://http?endpoint=not-a-url',
                'Invalid endpoint URL',
            ],
            'in-cluster with parameters' => [
                'kube://in-cluster?foo=bar',
                'The in-cluster provider does not accept any parameters',
            ],
            'kubeconfig without path' => [
                'kube://kubeconfig',
                'Missing required parameter "path"',
            ],
        ];
    }

    public function testParsedDsnGetParameter(): void
    {
        $parsed = $this->parser->parse('kube://http?endpoint=http://localhost&token=abc');

        $this->assertSame('http://localhost', $parsed->getParameter('endpoint'));
        $this->assertSame('abc', $parsed->getParameter('token'));
        $this->assertNull($parsed->getParameter('nonexistent'));
        $this->assertSame('default', $parsed->getParameter('nonexistent', 'default'));
    }

    public function testParsedDsnHasParameter(): void
    {
        $parsed = $this->parser->parse('kube://http?endpoint=http://localhost');

        $this->assertTrue($parsed->hasParameter('endpoint'));
        $this->assertFalse($parsed->hasParameter('token'));
    }
}
