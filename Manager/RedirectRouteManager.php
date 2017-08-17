<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Manager;

use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;

/**
 * Implementation of redirect-route manager.
 */
class RedirectRouteManager implements RedirectRouteManagerInterface
{
    /**
     * @var RedirectRouteRepositoryInterface
     */
    protected $redirectRouteRepository;

    /**
     * @param RedirectRouteRepositoryInterface $redirectRouteRepository
     */
    public function __construct(RedirectRouteRepositoryInterface $redirectRouteRepository)
    {
        $this->redirectRouteRepository = $redirectRouteRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RedirectRouteInterface $redirectRoute)
    {
        if ($this->redirectRouteRepository->findBySource($redirectRoute->getSource())) {
            throw new RedirectRouteNotUniqueException($redirectRoute->getSource());
        }

        $this->redirectRouteRepository->persist($redirectRoute);

        return $redirectRoute;
    }
}
