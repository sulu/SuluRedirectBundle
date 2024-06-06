<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Import\Writer;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\RedirectBundle\Exception\RedirectRouteNotUniqueException;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManagerInterface;
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
     * @var string[]
     */
    private $sources = [];

    /**
     * @var int
     */
    private $batchSize = 100;

    public function __construct(RedirectRouteManagerInterface $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    public function write(RedirectRouteInterface $entity): void
    {
        $this->validate($entity);
        $this->sources[] = strtolower($entity->getSource());

        try {
            $this->save($entity);
        } catch (RedirectRouteNotUniqueException $exception) {
            throw new DuplicatedSourceException($entity);
        }
    }

    public function finalize(): void
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
     */
    private function save(RedirectRouteInterface $entity): void
    {
        $this->manager->save($entity);

        if (0 === count($this->sources) % $this->batchSize) {
            $this->entityManager->flush();
        }
    }

    /**
     * Validate given redirect-route.
     *
     * @throws DuplicatedSourceException
     * @throws TargetIsEmptyException
     */
    private function validate(RedirectRouteInterface $entity): void
    {
        if ('' === $entity->getTarget() && Response::HTTP_GONE !== (int) $entity->getStatusCode()) {
            throw new TargetIsEmptyException($entity);
        }

        if (in_array(strtolower($entity->getSource()), $this->sources)) {
            throw new DuplicatedSourceException($entity);
        }
    }
}
