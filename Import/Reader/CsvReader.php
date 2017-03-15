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

use SplFileObject;

/**
 * Read a csv-file and stream each line as item into a callback.
 */
class CsvReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read($fileName)
    {
        $csv = new SplFileObject($fileName);
        $csv->setCsvControl();
        $csv->setFlags(SplFileObject::READ_CSV);

        $header = null;
        foreach ($csv as $lineNumber => $line) {
            if (count($line) === 1 && trim($line[0]) === '') {
                continue;
            }

            if ($lineNumber == 0) {
                $header = $line;

                continue;
            }

            yield new ReaderItem($lineNumber, '"' . implode('","', $line) . '"', $this->interpret($line, $header));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($fileName)
    {
        return pathinfo($fileName, PATHINFO_EXTENSION) === 'csv';
    }

    /**
     * Interpret given line.
     *
     * @param array $line
     * @param array $header
     *
     * @return array
     */
    private function interpret(array $line, array $header)
    {
        $item = [];
        foreach ($header as $index => $key) {
            $item[$key] = $line[$index];
        }

        return $item;
    }
}
