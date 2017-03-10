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
 * Raised when in a import process the same source should be created twice.
 */
class DuplicatedSourceException extends WriterException
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
        parent::__construct(sprintf('Source "%s" was imported twice.', $entity->getSource()));

        $this->entity = $entity;
    }
}
