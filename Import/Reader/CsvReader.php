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

use Sulu\Bundle\RedirectBundle\Import\Converter\Converter;

/**
 * Read a csv-file and stream each line as item into a callback.
 */
class CsvReader implements ReaderInterface
{
    /**
     * @return iterable<int, ReaderItem>
     */
    public function read($fileName)
    {
        ini_set('auto_detect_line_endings', true); // For mac's office excel csv

        $csv = new \SplFileObject($fileName);
        $csv->setCsvControl();
        $csv->setFlags(\SplFileObject::READ_CSV);

        $header = [Converter::SOURCE, Converter::TARGET, Converter::STATUS_CODE, Converter::ENABLED, Converter::SOURCE_HOST];
        /** @var string[] $line */
        foreach ($csv as $lineNumber => $line) {
            // ignore empty lines
            if (empty(array_filter($line))) {
                continue;
            }

            if (0 === $lineNumber) {
                if (false !== array_search(Converter::SOURCE, $line)) {
                    $header = $line;
                    continue;
                }
            }

            yield new ReaderItem($lineNumber, '"' . implode('","', $line) . '"', $this->interpret($line, $header));
        }
    }

    public function supports($fileName)
    {
        return 'csv' === pathinfo($fileName, PATHINFO_EXTENSION);
    }

    /**
     * Interpret given line.
     *
     * @param array<string|int, string> $line
     * @param array<string|int, string> $header
     *
     * @return array<string|int, string|null>
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
