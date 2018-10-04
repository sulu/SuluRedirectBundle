<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Import\Writer;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManagerInterface;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteNotUniqueException;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Write redirect-route entity to database by using the entity-manager.
 */
class Writer implements WriterInterface
{
    /**
     * @var RedirectRouteManagerInterface
     */
    private $manager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var array
     */
    private $sources = [];

    /**
     * @var int
     */
    private $batchSize = 100;

    /**
     * @param RedirectRouteManagerInterface $manager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(RedirectRouteManagerInterface $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(RedirectRouteInterface $entity)
    {
        $this->validate($entity);
        $this->sources[] = strtolower($entity->getSource());

        try {
            $this->save($entity);
        } catch (RedirectRouteNotUniqueException $exception) {
            throw new DuplicatedSourceException($entity);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finalize()
    {
        $this->entityManager->flush();
    }

    /**
     * Set batch-size.
     *
     * @param int $batchSize
     *
     * @return $this
     */
    public function setBatchSize($batchSize)
    {
        $this->batchSize = $batchSize;

        return $this;
    }

    /**
     * Save entity by using manager.
     *
     * @param RedirectRouteInterface $entity
     */
    private function save(RedirectRouteInterface $entity)
    {
        $this->manager->save($entity);

        if (0 === count($this->sources) % $this->batchSize) {
            $this->entityManager->flush();
        }
    }

    /**
     * Validate given redirect-route.
     *
     * @param RedirectRouteInterface $entity
     *
     * @throws DuplicatedSourceException
     * @throws TargetIsEmptyException
     */
    private function validate(RedirectRouteInterface $entity)
    {
        if ('' === $entity->getTarget() && Response::HTTP_GONE !== (int)$entity->getStatusCode()) {
            throw new TargetIsEmptyException($entity);
        }

        if (in_array(strtolower($entity->getSource()), $this->sources)) {
            throw new DuplicatedSourceException($entity);
        }
    }
}
