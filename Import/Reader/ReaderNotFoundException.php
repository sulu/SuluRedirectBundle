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

use Sulu\Bundle\RedirectBundle\Import\ImportException;

/**
 * This exception will be raised if no reader was found which supports the file.
 */
class ReaderNotFoundException extends ImportException
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        parent::__construct(sprintf('File "%s" is not supported', $fileName));

        $this->fileName = $fileName;
    }

    /**
     * Returns fileName.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
}
