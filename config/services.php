<?php

/*
 * This file is part of the P8P project.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use P8p\Bundle\Factory\ClientFactoryBuilder;
use P8p\Bundle\Factory\DsnParser;
use P8p\Client\Serializer\Normalizer\SchemaNormalizer;
use P8p\Client\Serializer\Normalizer\WatchEventDenormalizer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('p8p.dsn_parser', DsnParser::class);
    $services->set('p8p.client_factory_builder', ClientFactoryBuilder::class);
    $services->set(SchemaNormalizer::class)
        ->tag('serializer.normalizer', ['priority' => 500]);
    $services->set(WatchEventDenormalizer::class)
        ->tag('serializer.normalizer', ['priority' => 500]);
};
