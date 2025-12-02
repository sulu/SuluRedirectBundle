<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Import\Reader;

/**
 * Aggregates multiple readers.
 */
class ReaderFacade implements ReaderInterface
{
    /**
     * @param iterable<ReaderInterface> $readers
     */
    public function __construct(private iterable $readers = [])
    {
    }

    public function read($fileName)
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($fileName)) {
                return $reader->read($fileName);
            }
        }

        return null;
    }

    public function supports($fileName)
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($fileName)) {
                return true;
            }
        }

        return false;
    }
}
