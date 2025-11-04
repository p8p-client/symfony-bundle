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

readonly class ParsedDsn
{
    /**
     * @param array<string, string> $parameters
     */
    public function __construct(
        public string $provider,
        public array $parameters = [],
    ) {
    }

    public function getParameter(string $key, ?string $default = null): ?string
    {
        return $this->parameters[$key] ?? $default;
    }

    public function hasParameter(string $key): bool
    {
        return isset($this->parameters[$key]);
    }
}
