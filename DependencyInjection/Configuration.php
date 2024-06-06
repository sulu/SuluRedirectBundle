<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\DependencyInjection;

use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRouteRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Initializes configuration tree for redirect-bundle.
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sulu_redirect');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('gone_on_remove')
                    ->info('When enabled, this feature automatically creates redirects with http status code 410 when a document with route or an route entity is removed.')
                    ->canBeDisabled()
                ->end()
                ->arrayNode('imports')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')->defaultValue('%kernel.project_dir%/var/uploads/redirects')->end()
                    ->end()
                ->end()
                ->arrayNode('objects')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('redirect_route')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('model')->defaultValue(RedirectRoute::class)->end()
                                ->scalarNode('repository')->defaultValue(RedirectRouteRepository::class)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
