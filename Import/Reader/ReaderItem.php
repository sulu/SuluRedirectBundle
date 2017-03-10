<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Import\Reader;

/**
 * Container for reader result.
 */
class ReaderItem
{
    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @var array
     */
    private $data;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @param int $lineNumber
     * @param array $item
     * @param \Exception $exception
     */
    public function __construct($lineNumber, array $item = null, \Exception $exception = null)
    {
        $this->lineNumber = $lineNumber;
        $this->data = $item;
        $this->exception = $exception;
    }

    /**
     * Returns line.
     *
     * @return int
     */
    public function getLineNumber()
    {
        return $this->lineNumber;
    }

    /**
     * Returns item.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns exception.
     *
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
