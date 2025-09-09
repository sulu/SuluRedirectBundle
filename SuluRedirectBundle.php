<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle;

use Sulu\Bundle\PersistenceBundle\PersistenceBundleTrait;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Component\Route\RouteDefaultOptionsCompilerPass;
use Sulu\Component\Symfony\CompilerPass\TaggedServiceCollectorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Entry-point for redirect-bundle.
 */
class SuluRedirectBundle extends Bundle
{
    use PersistenceBundleTrait;

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new TaggedServiceCollectorCompilerPass(
                'sulu_redirect.import.aggregate_reader',
                'sulu_redirect.import.reader'
            )
        );
        $container->addCompilerPass(
            new TaggedServiceCollectorCompilerPass(
                'sulu_redirect.import.aggregate_converter',
                'sulu_redirect.import.converter'
            )
        );

        $container->addCompilerPass(
            new RouteDefaultOptionsCompilerPass('sulu_redirect.routing.provider', 1)
        );

        $this->buildPersistence(
            [
                RedirectRoute::class => 'sulu.model.redirect_route.class',
            ],
            $container
        );
    }
}
