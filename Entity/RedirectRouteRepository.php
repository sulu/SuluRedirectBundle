<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Entity;

use Doctrine\ORM\NoResultException;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Sulu\Component\Persistence\Repository\ORM\EntityRepository;

/**
 * Basic implementation of redirect-route repository.
 */
class RedirectRouteRepository extends EntityRepository implements RedirectRouteRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        /** @var RedirectRouteInterface|null $redirectRoute */
        $redirectRoute = $this->find($id);

        return $redirectRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function findEnabledBySource($source)
    {
        $query = $this->createQueryBuilder('redirect_route')
            ->andWhere('redirect_route.source = :source')
            ->andWhere('redirect_route.enabled = true')
            ->setParameter('source', $source)
            ->getQuery();

        try {
            return $query->getSingleResult();
        } catch (NoResultException $exception) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findBySource($source)
    {
        $query = $this->createQueryBuilder('redirect_route')
            ->andWhere('redirect_route.source = :source')
            ->setParameter('source', $source)
            ->getQuery();

        try {
            return $query->getSingleResult();
        } catch (NoResultException $exception) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function persist(RedirectRouteInterface $entity)
    {
        $this->_em->persist($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(RedirectRouteInterface $entity)
    {
        $this->_em->remove($entity);
    }
}
