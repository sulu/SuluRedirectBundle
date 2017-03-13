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

use Sulu\Bundle\RedirectBundle\Import\Converter\ConverterInterface;
use Sulu\Bundle\RedirectBundle\Import\Converter\ConverterNotFoundException;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderInterface;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderNotFoundException;
use Sulu\Bundle\RedirectBundle\Import\Writer\WriterInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;

/**
 * Import a file.
 */
class FileImport implements FileImportInterface
{
    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @param ReaderInterface $reader
     * @param ConverterInterface $converter
     * @param WriterInterface $writer
     */
    public function __construct(ReaderInterface $reader, ConverterInterface $converter, WriterInterface $writer)
    {
        $this->reader = $reader;
        $this->converter = $converter;
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function import($fileName)
    {
        if (!$this->reader->supports($fileName)) {
            throw new ReaderNotFoundException($fileName);
        }

        foreach ($this->reader->read($fileName) as $item) {
            if ($item->getException()) {
                yield new Item($item->getLineNumber(), null, $item->getException());

                continue;
            }

            try {
                yield new Item($item->getLineNumber(), $this->importItem($item->getData()));
            } catch (ImportException $exception) {
                yield new Item($item->getLineNumber(), null, $exception);
            }
        }

        $this->writer->finalize();
    }

    /**
     * Import given item.
     *
     * @param array $item
     *
     * @return RedirectRouteInterface
     *
     * @throws \Exception
     */
    private function importItem(array $item)
    {
        if (!$this->converter->supports($item)) {
            throw new ConverterNotFoundException($item);
        }

        $entity = $this->converter->convert($item);
        $this->writer->write($entity);

        return $entity;
    }
}
