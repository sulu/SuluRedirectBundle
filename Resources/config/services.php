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

use Sulu\Bundle\RedirectBundle\Admin\RedirectAdmin;
use Sulu\Bundle\RedirectBundle\Command\ImportCommand;
use Sulu\Bundle\RedirectBundle\Controller\RedirectRouteController;
use Sulu\Bundle\RedirectBundle\Controller\RedirectRouteImportController;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Symfony\Component\DependencyInjection\Reference;

return static function(ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sulu_redirect.redirect_route_controller', RedirectRouteController::class)
        ->public()
        ->args([
            new Reference('fos_rest.view_handler.default'),
            new Reference('sulu_core.doctrine_rest_helper'),
            new Reference('sulu_core.doctrine_list_builder_factory'),
            new Reference('sulu_core.list_builder.field_descriptor_factory'),
            new Reference('doctrine.orm.entity_manager'),
            new Reference('sulu_redirect.redirect_route_manager'),
            new Reference('sulu.repository.redirect_route'),
            '%sulu.model.redirect_route.class%',
        ])
        ->tag('sulu.context', ['context' => 'admin']);

    // sulu-admin
    $services->set('sulu_redirect.admin', RedirectAdmin::class)
        ->args([
            new Reference('sulu_admin.view_builder_factory'),
            new Reference('sulu_security.security_checker'),
        ])
        ->tag('sulu.admin')
        ->tag('sulu.context', ['context' => 'admin']);

    // redirect-route
    $services->set('sulu_redirect.redirect_route_manager', RedirectRouteManager::class)
        ->public()
        ->args([
            new Reference('sulu.repository.redirect_route'),
        ]);

    // import controller
    $services->set('sulu_redirect.controller.import', RedirectRouteImportController::class)
        ->public()
        ->args([
            new Reference('sulu_redirect.import'),
            '%sulu_redirect.imports.path%',
        ])
        ->tag('sulu.context', ['context' => 'admin']);

    $services->set('sulu_redirect.import_command', ImportCommand::class)
        ->args([
            new Reference('sulu_redirect.import'),
        ])
        ->tag('console.command');
};
