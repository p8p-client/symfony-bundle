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

use P8p\Client\Client;
use Psr\Container\ContainerInterface;

readonly class ClientRegistry
{
    /**
     * @param array<string, string> $clients Map of client names to service IDs
     */
    public function __construct(
        private ContainerInterface $locator,
        private array $clients,
        private string $defaultClient,
    ) {
    }

    public function get(string $name): Client
    {
        if (!isset($this->clients[$name])) {
            throw new \InvalidArgumentException(sprintf('Client "%s" does not exist. Available clients: %s', $name, implode(', ', array_keys($this->clients))));
        }

        return $this->locator->get($this->clients[$name]); /* @phpstan-ignore return.type */
    }

    public function getDefault(): Client
    {
        return $this->get($this->defaultClient);
    }

    public function has(string $name): bool
    {
        return isset($this->clients[$name]);
    }

    /**
     * @return array<string>
     */
    public function getClientNames(): array
    {
        return array_keys($this->clients);
    }
}
