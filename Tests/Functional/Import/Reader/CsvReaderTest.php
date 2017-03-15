<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Functional\Import\Reader;

use Sulu\Bundle\RedirectBundle\Import\Reader\CsvReader;

class CsvReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->fileName = __DIR__ . '/import.csv';
    }

    public function testSupports()
    {
        $reader = new CsvReader();

        $this->assertTrue($reader->supports($this->fileName));
    }

    public function testSupportsUnsupportedFileExtension()
    {
        $reader = new CsvReader();

        $this->assertFalse($reader->supports(__DIR__ . '/import.txt'));
    }

    public function testRead()
    {
        $reader = new CsvReader();

        $i = 1;
        foreach ($reader->read($this->fileName) as $item) {
            $this->assertEquals('/source-' . $i, $item->getData()['source']);
            $this->assertEquals('/target-' . $i, $item->getData()['target']);

            ++$i;
        }
    }
}
