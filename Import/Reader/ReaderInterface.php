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
 * File-Reader which is able to stream each item.
 */
interface ReaderInterface
{
    /**
     * Read file-content and convert it to associative array.
     *
     * @param string $fileName
     *
     * @return ReaderItem[]
     */
    public function read($fileName);

    /**
     * Returns true if reader supports file.
     *
     * @param string $fileName
     *
     * @return bool
     */
    public function supports($fileName);
}
