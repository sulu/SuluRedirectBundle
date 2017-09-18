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

use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;

/**
 * Target cannot be empty.
 */
class TargetIsEmptyException extends WriterException implements \JsonSerializable
{
    /**
     * @var RedirectRouteInterface
     */
    private $entity;

    /**
     * @param RedirectRouteInterface $entity
     */
    public function __construct(RedirectRouteInterface $entity)
    {
        parent::__construct(sprintf('Target for source "%s" cannot be empty.', $entity->getSource()));

        $this->entity = $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'translationKey' => 'sulu_redirect.exceptions.target_empty',
            'source' => $this->entity->getSource(),
        ];
    }
}
