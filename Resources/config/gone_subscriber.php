<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sulu\Bundle\RedirectBundle\GoneSubscriber\GoneEntitySubscriber;
use Symfony\Component\DependencyInjection\Reference;

return static function(ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sulu_redirect.subscriber.entity', GoneEntitySubscriber::class)
        ->args([
            new Reference('sulu_route.route_repository'),
        ])
        ->tag('doctrine.event_listener', ['event' => 'preRemove', 'method' => 'preRemove'])
        ->tag('doctrine.event_listener', ['event' => 'postFlush', 'method' => 'postFlush', 'priority' => 10])
        ->tag('doctrine.event_listener', ['event' => 'onClear', 'method' => 'onClear'])
        ->tag('kernel.reset', ['method' => 'reset']);
};
