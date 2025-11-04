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

use P8p\Bundle\Exception\InvalidDsnException;
use P8p\Bundle\Factory\ClientFactoryBuilder;
use P8p\Client\ClientFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ClientFactoryBuilderTest extends TestCase
{
    private ClientFactoryBuilder $builder;
    private MockHttpClient $httpClient;
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->builder = new ClientFactoryBuilder();
        $this->httpClient = new MockHttpClient();
        $this->serializer = new Serializer(
            [new ObjectNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder()]
        );
    }

    public function testBuildHttpClientFactory(): void
    {
        $dsn = 'kube://http?endpoint=http://127.0.0.1:8001';

        $factory = $this->builder->build($dsn, $this->httpClient, $this->serializer);

        $this->assertInstanceOf(ClientFactory::class, $factory);
    }

    public function testBuildHttpClientFactoryWithToken(): void
    {
        $dsn = 'kube://http?endpoint=https://api.k8s.local:6443&token=my-secret-token';

        $factory = $this->builder->build($dsn, $this->httpClient, $this->serializer);

        $this->assertInstanceOf(ClientFactory::class, $factory);
    }

    public function testBuildHttpClientFactoryWithAllParameters(): void
    {
        $caFile = tempnam(sys_get_temp_dir(), 'ca');
        $certFile = tempnam(sys_get_temp_dir(), 'cert');
        $keyFile = tempnam(sys_get_temp_dir(), 'key');

        try {
            $dsn = sprintf(
                'kube://http?endpoint=https://api.k8s.local:6443&token=token&ca=%s&cert=%s&key=%s&http_user=admin&http_password=secret',
                $caFile,
                $certFile,
                $keyFile
            );

            $factory = $this->builder->build($dsn, $this->httpClient, $this->serializer);

            $this->assertInstanceOf(ClientFactory::class, $factory);
        } finally {
            @unlink($caFile);
            @unlink($certFile);
            @unlink($keyFile);
        }
    }

    public function testBuildInClusterFactory(): void
    {
        $dsn = 'kube://in-cluster';

        $factory = $this->builder->build($dsn, $this->httpClient, $this->serializer);

        $this->assertInstanceOf(ClientFactory::class, $factory);
    }

    public function testBuildKubeConfigFactory(): void
    {
        $configFile = tempnam(sys_get_temp_dir(), 'kubeconfig');
        file_put_contents($configFile, 'dummy content');

        try {
            $dsn = sprintf('kube://kubeconfig?path=%s', $configFile);

            $factory = $this->builder->build($dsn, $this->httpClient, $this->serializer);

            $this->assertInstanceOf(ClientFactory::class, $factory);
        } finally {
            @unlink($configFile);
        }
    }

    public function testBuildKubeConfigFactoryWithContext(): void
    {
        $configFile = tempnam(sys_get_temp_dir(), 'kubeconfig');
        file_put_contents($configFile, 'dummy content');

        try {
            $dsn = sprintf('kube://kubeconfig?path=%s&context=production', $configFile);

            $factory = $this->builder->build($dsn, $this->httpClient, $this->serializer);

            $this->assertInstanceOf(ClientFactory::class, $factory);
        } finally {
            @unlink($configFile);
        }
    }

    public function testBuildWithInvalidProvider(): void
    {
        $dsn = 'kube://invalid-provider';

        $this->expectException(InvalidDsnException::class);
        $this->expectExceptionMessage('Unknown provider: "invalid-provider"');

        $this->builder->build($dsn, $this->httpClient, $this->serializer);
    }

    public function testBuildWithMissingCaFile(): void
    {
        $dsn = 'kube://http?endpoint=https://api.k8s.local:6443&ca=/nonexistent/ca.crt';

        $this->expectException(InvalidDsnException::class);
        $this->expectExceptionMessage('File not found: "/nonexistent/ca.crt"');

        $this->builder->build($dsn, $this->httpClient, $this->serializer);
    }

    public function testBuildWithMissingCertFile(): void
    {
        $dsn = 'kube://http?endpoint=https://api.k8s.local:6443&cert=/nonexistent/cert.crt';

        $this->expectException(InvalidDsnException::class);
        $this->expectExceptionMessage('File not found: "/nonexistent/cert.crt"');

        $this->builder->build($dsn, $this->httpClient, $this->serializer);
    }

    public function testBuildWithMissingKeyFile(): void
    {
        $dsn = 'kube://http?endpoint=https://api.k8s.local:6443&key=/nonexistent/key.pem';

        $this->expectException(InvalidDsnException::class);
        $this->expectExceptionMessage('File not found: "/nonexistent/key.pem"');

        $this->builder->build($dsn, $this->httpClient, $this->serializer);
    }

    public function testResolveFilePathValidatesExistence(): void
    {
        // This tests the file validation indirectly through the DSN parsing
        $nonexistentFile = '/tmp/definitely-does-not-exist-'.uniqid().'.txt';

        $dsn = sprintf('kube://http?endpoint=http://localhost:8001&ca=%s', $nonexistentFile);

        $this->expectException(InvalidDsnException::class);
        $this->expectExceptionMessage('File not found');

        $this->builder->build($dsn, $this->httpClient, $this->serializer);
    }
}
