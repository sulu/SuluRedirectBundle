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

use Doctrine\ORM\QueryBuilder;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Sulu\Component\Persistence\Repository\ORM\EntityRepository;

/**
 * Basic implementation of redirect-route repository.
 */
class RedirectRouteRepository extends EntityRepository implements RedirectRouteRepositoryInterface
{
    public function findById($id)
    {
        /** @var RedirectRouteInterface|null $redirectRoute */
        $redirectRoute = $this->find($id);

        return $redirectRoute;
    }

    public function findEnabledBySource($source, $sourceHost = null)
    {
        $queryBuilder = $this->createFindBySourceQueryBuilder($source, $sourceHost);
        $queryBuilder->andWhere('redirect_route.enabled = true');

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findBySource($source, $sourceHost = null)
    {
        $queryBuilder = $this->createFindBySourceQueryBuilder($source, $sourceHost);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function persist(RedirectRouteInterface $entity): void
    {
        $this->_em->persist($entity);
    }

    public function remove(RedirectRouteInterface $entity): void
    {
        $this->_em->remove($entity);
    }

    /**
     * @param string $source
     * @param string|null $sourceHost
     *
     * @return QueryBuilder
     */
    private function createFindBySourceQueryBuilder($source, $sourceHost = null)
    {
        $queryBuilder = $this->createQueryBuilder('redirect_route')
            ->andWhere('redirect_route.source = :source')
            ->setParameter('source', mb_strtolower('/' . ltrim($source, '/')))
            ->orderBy('redirect_route.sourceHost', 'DESC')
            ->setMaxResults(1);

        if (!empty($sourceHost)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('redirect_route.sourceHost', ':sourceHost'),
                    $queryBuilder->expr()->eq('redirect_route.sourceHost', "''"),
                    $queryBuilder->expr()->isNull('redirect_route.sourceHost')
                )
            );

            $queryBuilder->setParameter('sourceHost', mb_strtolower($sourceHost));
        }

        return $queryBuilder;
    }
}
