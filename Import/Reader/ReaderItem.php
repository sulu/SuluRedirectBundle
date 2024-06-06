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
    private $lineContent;

    /**
     * @var array<string, mixed>
     */
    private $data;

    /**
     * @var ImportException|null
     */
    private $exception;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(int $lineNumber, string $lineContent, array $data, ?ImportException $exception = null)
    {
        $this->lineNumber = $lineNumber;
        $this->lineContent = $lineContent;
        $this->data = $data;
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
     * @return array<string, mixed>
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns exception.
     *
     * @return ImportException|null
     */
    public function getException()
    {
        return $this->exception;
    }
}
