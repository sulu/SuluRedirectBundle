<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Entity;

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
        return $this->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findEnabledBySource($source, $sourceHost = null)
    {
        $queryBuilder = $this->createFindBySourceQueryBuilder($source, $sourceHost);
        $queryBuilder->andWhere('redirect_route.enabled = true');

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySource($source, $sourceHost = null)
    {
        $queryBuilder = $this->createFindBySourceQueryBuilder($source, $sourceHost);

        return $queryBuilder->getQuery()->getOneOrNullResult();
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
            ->setParameter('source', $source)
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

            $queryBuilder->setParameter('sourceHost', $sourceHost);
        }

        return $queryBuilder;
    }
}
