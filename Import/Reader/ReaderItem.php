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
     * @var string
     */
    private $content;

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
     * @param string $lineContent
     * @param array $item
     * @param \Exception $exception
     */
    public function __construct($lineNumber, $lineContent, array $item = null, \Exception $exception = null)
    {
        $this->lineNumber = $lineNumber;
        $this->data = $item;
        $this->exception = $exception;
        $this->lineContent = $lineContent;
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
     * Returns content.
     *
     * @return string
     */
    public function getLineContent()
    {
        return $this->lineContent;
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
