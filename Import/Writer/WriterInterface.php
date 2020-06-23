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

use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;

/**
 * Will be used a sink for redirect-route import process.
 */
interface WriterInterface
{
    /**
     * Write given redirect-route to destination.
     */
    public function write(RedirectRouteInterface $entity): void;

    /**
     * Will be called after importing last entity..
     */
    public function finalize(): void;
}
