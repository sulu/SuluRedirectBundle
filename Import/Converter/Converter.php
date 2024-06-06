<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Import\Converter;

use Ramsey\Uuid\Uuid;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Converts simple array to route-repository entity.
 */
class Converter implements ConverterInterface
{
    public const SOURCE = 'source';

    public const SOURCE_HOST = 'sourceHost';

    public const TARGET = 'target';

    public const STATUS_CODE = 'statusCode';

    public const ENABLED = 'enabled';

    /**
     * @var RedirectRouteRepositoryInterface
     */
    private $repository;

    public function __construct(RedirectRouteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function convert(array $item)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        /** @var string $source */
        $source = $item[self::SOURCE];
        $entity = $this->repository->findBySource($source);
        if (!$entity) {
            /** @var RedirectRouteInterface $entity */
            $entity = $this->repository->createNew();
            $entity->setId(Uuid::uuid4()->toString());
        }

        foreach ([self::SOURCE, self::TARGET, self::STATUS_CODE, self::ENABLED, self::SOURCE_HOST] as $field) {
            if (!array_key_exists($field, $item) || null === $item[$field]) {
                continue;
            }

            $accessor->setValue($entity, $field, $item[$field]);
        }

        return $entity;
    }

    public function supports(array $item)
    {
        $keys = array_keys($item);
        if (!in_array(self::SOURCE, $keys) || !in_array(self::TARGET, $keys)) {
            return false;
        }

        return true;
    }
}
