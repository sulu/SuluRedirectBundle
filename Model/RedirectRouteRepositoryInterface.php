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

/**
 * Provides queries for redirect-route.
 */
interface RedirectRouteRepositoryInterface
{
    /**
     * Find enabled redirect-routes for given source.
     *
     * @param string $source
     *
     * @return RedirectRouteInterface
     */
    public function findEnabledBySource($source);
}
