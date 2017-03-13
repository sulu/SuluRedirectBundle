<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle;

use Sulu\Bundle\PersistenceBundle\PersistenceBundleTrait;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Component\Symfony\CompilerPass\TaggedServiceCollectorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Entry-point for redirect-bundle.
 */
class SuluRedirectBundle extends Bundle
{
    use PersistenceBundleTrait;

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
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

        $this->buildPersistence(
            [
               RedirectRoute::class => 'sulu.model.redirect_route.class',
            ],
            $container
        );
    }
}
