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

class DsnParser
{
    /**
     * Parse a Kubernetes DSN.
     *
     * Supported formats:
     * - kube://http?endpoint=<url>&token=<path|value>&ca=<path>&cert=<path>&key=<path>
     * - kube://in-cluster
     * - kube://kubeconfig?path=<filepath>&context=<name>
     */
    public function parse(string $dsn): ParsedDsn
    {
        if (!str_starts_with($dsn, 'kube://')) {
            throw new InvalidDsnException(sprintf('Invalid DSN scheme. Expected "kube://", got "%s"', $dsn));
        }

        $parsed = parse_url($dsn);

        if (false === $parsed || !isset($parsed['host']) || empty($parsed['host'])) {
            throw new InvalidDsnException(sprintf('Missing provider in DSN: "%s"', $dsn));
        }

        $provider = $parsed['host'];

        $parameters = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $parameters);
        }

        $this->validateDsn($provider, $parameters); /* @phpstan-ignore argument.type */

        return new ParsedDsn($provider, $parameters); /* @phpstan-ignore argument.type */
    }

    /**
     * @param array<string, string> $parameters
     *
     * @throws InvalidDsnException
     */
    private function validateDsn(string $provider, array $parameters): void
    {
        match ($provider) {
            'http' => $this->validateHttpDsn($parameters),
            'in-cluster' => $this->validateInClusterDsn($parameters),
            'kubeconfig' => $this->validateKubeConfigDsn($parameters),
            default => throw new InvalidDsnException(sprintf('Unknown provider: "%s". Supported providers: http, in-cluster, kubeconfig', $provider)),
        };
    }

    /**
     * @param array<string, string> $parameters
     */
    private function validateHttpDsn(array $parameters): void
    {
        if (!isset($parameters['endpoint'])) {
            throw new InvalidDsnException('Missing required parameter "endpoint" for http provider');
        }

        $endpoint = $parameters['endpoint'];
        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            throw new InvalidDsnException(sprintf('Invalid endpoint URL: "%s"', $endpoint));
        }
    }

    /**
     * @param array<string, string> $parameters
     */
    private function validateInClusterDsn(array $parameters): void
    {
        if (!empty($parameters)) {
            throw new InvalidDsnException('The in-cluster provider does not accept any parameters');
        }
    }

    /**
     * @param array<string, string> $parameters
     */
    private function validateKubeConfigDsn(array $parameters): void
    {
        if (!isset($parameters['path'])) {
            throw new InvalidDsnException('Missing required parameter "path" for kubeconfig provider');
        }
    }
}
