<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Import;

/**
 * Interface for file-importer.
 */
interface FileImportInterface
{
    /**
     * Import given file.
     *
     * @param string $fileName
     *
     * @return Item[]
     */
    public function import($fileName);
}
