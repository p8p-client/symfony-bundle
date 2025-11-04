<?php

/*
 * This file is part of the P8P project.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace P8p\Bundle\Factory;

use P8p\Bundle\Exception\InvalidDsnException;
use P8p\Client\ClientFactory;
use P8p\Client\Credentials\InClusterProvider;
use P8p\Client\Credentials\KubeConfigProvider;
use P8p\Client\Credentials\UrlProvider;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientFactoryBuilder
{
    public function __construct(
        private readonly DsnParser $dsnParser = new DsnParser(),
    ) {
    }

    /**
     * Build a ClientFactory from a DSN string.
     *
     * @param string               $dsn        DSN string (e.g., kube://http?endpoint=...)
     * @param HttpClientInterface  $httpClient Base HTTP client from Symfony
     * @param ?SerializerInterface $serializer Custom serializer (optional)
     */
    public function build(
        string $dsn,
        HttpClientInterface $httpClient,
        ?SerializerInterface $serializer = null,
    ): ClientFactory {
        $parsedDsn = $this->dsnParser->parse($dsn);

        return match ($parsedDsn->provider) {
            'http' => $this->buildHttpClientFactory($parsedDsn, $httpClient, $serializer),
            'in-cluster' => $this->buildInClusterFactory($httpClient, $serializer),
            'kubeconfig' => $this->buildKubeConfigFactory($parsedDsn, $httpClient, $serializer),
            default => throw new InvalidDsnException(sprintf('Unknown provider: "%s"', $parsedDsn->provider)),
        };
    }

    private function buildHttpClientFactory(
        ParsedDsn $dsn,
        HttpClientInterface $httpClient,
        ?SerializerInterface $serializer,
    ): ClientFactory {
        // endpoint is already validated by DsnParser
        $endpoint = $dsn->getParameter('endpoint');
        $token = $dsn->getParameter('token');
        $caFile = $dsn->hasParameter('ca') ? $this->resolveFilePath($dsn->getParameter('ca')) : null;
        $certFile = $dsn->hasParameter('cert') ? $this->resolveFilePath($dsn->getParameter('cert')) : null;
        $keyFile = $dsn->hasParameter('key') ? $this->resolveFilePath($dsn->getParameter('key')) : null;
        $httpUser = $dsn->getParameter('http_user');
        $httpPassword = $dsn->getParameter('http_password');

        $provider = new UrlProvider(
            endpoint: $endpoint, /* @phpstan-ignore argument.type */
            token: $token,
            caFile: $caFile,
            certificationFile: $certFile,
            privateKeyFile: $keyFile,
            httpUser: $httpUser,
            httpPassword: $httpPassword,
        );

        return new ClientFactory($provider, $httpClient, $serializer);
    }

    private function buildInClusterFactory(
        HttpClientInterface $httpClient,
        ?SerializerInterface $serializer,
    ): ClientFactory {
        return new ClientFactory(new InClusterProvider(), $httpClient, $serializer);
    }

    private function buildKubeConfigFactory(
        ParsedDsn $dsn,
        HttpClientInterface $httpClient,
        ?SerializerInterface $serializer,
    ): ClientFactory {
        /** @var string $path */
        $path = $dsn->getParameter('path');
        $context = $dsn->getParameter('context');

        return new ClientFactory(
            new KubeConfigProvider($path, $context),
            $httpClient,
            $serializer
        );
    }

    private function resolveFilePath(?string $path): ?string
    {
        if (null === $path) {
            return null;
        }

        // Validate that the file exists
        if (!file_exists($path)) {
            throw new InvalidDsnException(sprintf('File not found: "%s"', $path));
        }

        return $path;
    }
}
