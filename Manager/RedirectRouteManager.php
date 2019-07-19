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

use Ramsey\Uuid\Uuid;
use Sulu\Bundle\RedirectBundle\Exception\RedirectRouteNotFoundException;
use Sulu\Bundle\RedirectBundle\Exception\RedirectRouteNotUniqueException;
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
    public function saveByData($data)
    {
        $source = $data['source'];
        $id = $data['id'] ?? null;

        $otherRoute = $this->redirectRouteRepository->findBySource($source);

        // load existing tag if id is given and create a new one otherwise
        if ($id) {
            $redirectRoute = $this->redirectRouteRepository->findById($id);
            if (!$redirectRoute) {
                throw new RedirectRouteNotFoundException($id);
            }
        } else {
            $redirectRoute = $this->redirectRouteRepository->createNew();
            $redirectRoute->setId(Uuid::uuid4()->toString());
        }

        if ($otherRoute && $otherRoute->getId() !== $redirectRoute->getId()) {
            throw new RedirectRouteNotUniqueException($source);
        }

        // update data
        $redirectRoute->setSource($data['source']);
        $redirectRoute->setTarget($data['target']);
        $redirectRoute->setEnabled($data['enabled']);
        $redirectRoute->setStatusCode($data['statusCode']);

        if (410 === $redirectRoute->getStatusCode()) {
            $redirectRoute->setTarget('');
        }

        $this->redirectRouteRepository->persist($redirectRoute);


        return $redirectRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RedirectRouteInterface $redirectRoute)
    {
        $otherRoute = $this->redirectRouteRepository->findBySource($redirectRoute->getSource());

        if (!$redirectRoute->getId()) {
            $redirectRoute->setId(Uuid::uuid4()->toString());
        }

        if ($otherRoute && $otherRoute->getId() !== $redirectRoute->getId()) {
            throw new RedirectRouteNotUniqueException($redirectRoute->getSource());
        }

        if (410 === $redirectRoute->getStatusCode()) {
            $redirectRoute->setTarget('');
        }

        $this->redirectRouteRepository->persist($redirectRoute);

        return $redirectRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RedirectRouteInterface $redirectRoute)
    {
        $this->redirectRouteRepository->remove($redirectRoute);
    }
}
