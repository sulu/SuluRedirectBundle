<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Manager;

use Sulu\Bundle\RedirectBundle\Exception\RedirectRouteNotUniqueException;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;

/**
 * Interface for redirect-route manager.
 */
interface RedirectRouteManagerInterface
{
    /**
     * Save given redirect-route.
     *
     * @param array $data The data of the tag to save
     *
     * @return RedirectRouteInterface
     *
     * @throws RedirectRouteNotUniqueException
     */
    public function saveByData($data);

    /**
     * Delete given redirect-route.
     *
     * @param RedirectRouteInterface $redirectRoute
     */
    public function delete(RedirectRouteInterface $redirectRoute);

    /**
     * Save given redirect-route.
     *
     * @param RedirectRouteInterface $redirectRoute
     *
     * @return RedirectRouteInterface
     *
     * @throws RedirectRouteNotUniqueException
     */
    public function save(RedirectRouteInterface $redirectRoute);
}
