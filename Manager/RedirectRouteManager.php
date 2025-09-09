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

    public function __construct(RedirectRouteRepositoryInterface $redirectRouteRepository)
    {
        $this->redirectRouteRepository = $redirectRouteRepository;
    }

    public function saveByData($data)
    {
        $source = $data['source'];
        $sourceHost = $data['sourceHost'];
        $id = $data['id'] ?? null;

        $otherRoute = $this->redirectRouteRepository->findBySource($source, $sourceHost);

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

        // update data
        $redirectRoute->setSource($data['source']);
        $redirectRoute->setSourceHost($data['sourceHost']);
        $redirectRoute->setTarget((string) $data['target']);
        $redirectRoute->setStatusCode($data['statusCode']);

        if (410 === $redirectRoute->getStatusCode()) {
            $redirectRoute->setTarget('');
        }

        if (
            $otherRoute
            && $otherRoute->getId() !== $redirectRoute->getId()
            && $otherRoute->getSourceHost() === $redirectRoute->getSourceHost()
        ) {
            throw new RedirectRouteNotUniqueException($source, $sourceHost);
        }

        $this->redirectRouteRepository->persist($redirectRoute);

        return $redirectRoute;
    }

    public function save(RedirectRouteInterface $redirectRoute)
    {
        $otherRoute = $this->redirectRouteRepository->findBySource($redirectRoute->getSource(), $redirectRoute->getSourceHost());

        if (!$redirectRoute->getId()) {
            $redirectRoute->setId(Uuid::uuid4()->toString());
        }

        if (
            $otherRoute
            && $otherRoute->getId() !== $redirectRoute->getId()
            && $otherRoute->getSourceHost() === $redirectRoute->getSourceHost()
        ) {
            throw new RedirectRouteNotUniqueException($redirectRoute->getSource(), $redirectRoute->getSourceHost());
        }

        if (410 === $redirectRoute->getStatusCode()) {
            $redirectRoute->setTarget('');
        }

        $this->redirectRouteRepository->persist($redirectRoute);

        return $redirectRoute;
    }

    public function delete(RedirectRouteInterface $redirectRoute): void
    {
        $this->redirectRouteRepository->remove($redirectRoute);
    }
}
