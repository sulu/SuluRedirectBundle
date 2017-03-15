<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Import\Converter;

use Ramsey\Uuid\Uuid;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Converts simple array to route-repository entity.
 */
class Converter implements ConverterInterface
{
    const SOURCE = 'source';
    const TARGET = 'target';
    const STATUS_CODE = 'statusCode';
    const ENABLED = 'enabled';

    /**
     * @var RedirectRouteRepositoryInterface
     */
    private $repository;

    /**
     * @param RedirectRouteRepositoryInterface $repository
     */
    public function __construct(RedirectRouteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $item)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $entity = $this->repository->findBySource($item[self::SOURCE]);
        if (!$entity) {
            $entity = $this->repository->createNew();
            $entity->setId(Uuid::uuid4()->toString());
        }

        foreach ([self::SOURCE, self::TARGET, self::STATUS_CODE, self::ENABLED] as $field) {
            if (!array_key_exists($field, $item)) {
                continue;
            }

            $accessor->setValue($entity, $field, $item[$field]);
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(array $item)
    {
        $keys = array_keys($item);
        if (!in_array(self::SOURCE, $keys) || !in_array(self::TARGET, $keys)) {
            return false;
        }

        return true;
    }
}
