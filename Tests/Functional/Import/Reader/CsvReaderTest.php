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
    public function testSupports()
    {
        $reader = new CsvReader();
        $fileName = __DIR__ . '/import.csv';

        $this->assertTrue($reader->supports($fileName));
    }

    public function testSupportsUnsupportedFileExtension()
    {
        $reader = new CsvReader();

        $this->assertFalse($reader->supports(__DIR__ . '/import.txt'));
    }

    public function testRead()
    {
        $reader = new CsvReader();
        $fileName = __DIR__ . '/import.csv';

        $i = 1;
        foreach ($reader->read($fileName) as $item) {
            $this->assertEquals('/source-' . $i, $item->getData()['source']);
            $this->assertEquals('/target-' . $i, $item->getData()['target']);

            ++$i;
        }

        $this->assertSame(4, $i);
    }

    public function testReadWithoutHeader()
    {
        $reader = new CsvReader();
        $fileName = __DIR__ . '/import-without-header.csv';

        $i = 1;
        foreach ($reader->read($fileName) as $item) {
            $this->assertEquals('/source-' . $i, $item->getData()['source']);
            $this->assertEquals('/target-' . $i, $item->getData()['target']);

            ++$i;
        }

        $this->assertSame(1, $i);
    }

    public function testReadDifferentRows()
    {
        $expected = [
            ['source' => '/source-1', 'target' => '/target-1', 'statusCode' => 302, 'enabled' => 1],
            ['source' => '/source-2', 'target' => '/target-2', 'statusCode' => 302, 'enabled' => null],
            ['source' => '/source-3', 'target' => '/target-3', 'statusCode' => null, 'enabled' => null],
        ];

        $reader = new CsvReader();
        $fileName = __DIR__ . '/import_different_columns.csv';

        $i = 0;
        foreach ($reader->read($fileName) as $item) {
            $this->assertEquals($expected[$i]['source'], $item->getData()['source']);
            $this->assertEquals($expected[$i]['target'], $item->getData()['target']);
            $this->assertEquals($expected[$i]['statusCode'], $item->getData()['statusCode']);
            $this->assertEquals($expected[$i]['enabled'], $item->getData()['enabled']);

            ++$i;
        }

        $this->assertSame(3, $i);
    }
}
