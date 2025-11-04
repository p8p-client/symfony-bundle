<?php

/*
 * This file is part of the P8P project.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('p8p', [
        'clients' => [
            'default' => [
                'dsn' => 'kube://http?endpoint=http://127.0.0.1:8001',
            ],
            'other' => [
                'dsn' => 'kube://kubeconfig?path=/Users/julien/.kube/config',
            ],
        ],
        'default_client' => 'default',
    ]);
};
