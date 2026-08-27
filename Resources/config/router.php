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

use Sulu\Bundle\RedirectBundle\Controller\WebsiteRedirectController;
use Sulu\Bundle\RedirectBundle\Routing\RedirectRouteCollectionLoader;
use Symfony\Component\DependencyInjection\Reference;

return static function(ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sulu_redirect.routing.loader', RedirectRouteCollectionLoader::class)
        ->args([
            new Reference('sulu.repository.redirect_route'),
        ])
        ->tag('sulu_route.route_collection_for_request_loader', ['priority' => 5])
        ->tag('sulu.context', ['context' => 'website']);

    $services->set('sulu_redirect.controller.redirect', WebsiteRedirectController::class)
        ->public()
        ->tag('sulu.context', ['context' => 'website']);
};
