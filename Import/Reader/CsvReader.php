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
use Sulu\Bundle\RedirectBundle\Import\Converter\Converter;

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
        ini_set('auto_detect_line_endings', true); // For mac's office excel csv

        $csv = new SplFileObject($fileName);
        $csv->setCsvControl();
        $csv->setFlags(SplFileObject::READ_CSV);

        $header = [Converter::SOURCE, Converter::TARGET, Converter::STATUS_CODE, Converter::ENABLED];
        foreach ($csv as $lineNumber => $line) {
            if (1 === count($line) && '' === trim($line[0])) {
                continue;
            }

            if (0 == $lineNumber) {
                if (false !== array_search(Converter::SOURCE, $line)) {
                    $header = $line;
                    continue;
                }
            }

            yield new ReaderItem($lineNumber, '"' . implode('","', $line) . '"', $this->interpret($line, $header));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($fileName)
    {
        return 'csv' === pathinfo($fileName, PATHINFO_EXTENSION);
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
            $item[$key] = array_key_exists($index, $line) ? $line[$index] : null;
        }

        return $item;
    }
}
