<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Model;

use Sulu\Component\Persistence\Repository\RepositoryInterface;

/**
 * Provides queries for redirect-route.
 */
interface RedirectRouteRepositoryInterface extends RepositoryInterface
{
    /**
     * Find enabled redirect-routes for given source.
     *
     * @param string $source
     *
     * @return RedirectRouteInterface
     */
    public function findEnabledBySource($source);

    /**
     * Find redirect-routes for given source.
     *
     * @param string $source
     *
     * @return RedirectRouteInterface
     */
    public function findBySource($source);

    /**
     * Persist given entity.
     *
     * @param RedirectRouteInterface $entity
     */
    public function persist(RedirectRouteInterface $entity);
}
