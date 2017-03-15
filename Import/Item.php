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

use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;

/**
 * Container for import result.
 */
class Item
{
    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @var string
     */
    private $lineContent;

    /**
     * @var RedirectRouteInterface
     */
    private $data;

    /**
     * @var ImportException
     */
    private $exception;

    /**
     * @param int $lineNumber
     * @param string $lineContent
     * @param RedirectRouteInterface $data
     * @param ImportException $exception
     */
    public function __construct($lineNumber, $lineContent, RedirectRouteInterface $data = null, ImportException $exception = null)
    {
        $this->lineNumber = $lineNumber;
        $this->lineContent = $lineContent;
        $this->data = $data;
        $this->exception = $exception;
    }

    /**
     * Returns line-number.
     *
     * @return int
     */
    public function getLineNumber()
    {
        return $this->lineNumber;
    }

    /**
     * Returns line-content.
     *
     * @return string
     */
    public function getLineContent()
    {
        return $this->lineContent;
    }

    /**
     * Returns data.
     *
     * @return RedirectRouteInterface
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns exception.
     *
     * @return ImportException
     */
    public function getException()
    {
        return $this->exception;
    }
}
