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
    $container->extension('framework', [
        'secret' => 'secret',
        'session' => [
            'handler_id' => null,
        ],
    ]);

    if ('test' === $container->env()) {
        $container->extension('framework', [
            'session' => [
                'storage_factory_id' => 'session.storage.factory.mock_file',
            ],
            'test' => true,
        ]);
    }
};
