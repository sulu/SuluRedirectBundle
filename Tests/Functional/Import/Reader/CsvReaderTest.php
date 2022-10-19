<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Functional\Import\Reader;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\RedirectBundle\Import\Reader\CsvReader;

class CsvReaderTest extends TestCase
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

        $i = 0;
        foreach ($reader->read($fileName) as $item) {
            ++$i;

            $this->assertEquals('/source-' . $i, $item->getData()['source']);
            $this->assertEquals('/target-' . $i, $item->getData()['target']);
        }

        $this->assertSame(4, $i);
    }

    public function testReadWithoutHeader()
    {
        $reader = new CsvReader();
        $fileName = __DIR__ . '/import-without-header.csv';

        $i = 0;
        foreach ($reader->read($fileName) as $item) {
            ++$i;

            $this->assertEquals('/source-' . $i, $item->getData()['source']);
            $this->assertEquals('/target-' . $i, $item->getData()['target']);
        }

        $this->assertSame(1, $i);
    }

    public function testReadDifferentRows()
    {
        $expected = [
            ['source' => '/source-1', 'target' => '/target-1', 'statusCode' => 302, 'enabled' => 1, 'sourceHost' => 'example.com'],
            ['source' => '/source-2', 'target' => '/target-2', 'statusCode' => 302, 'enabled' => 1, 'sourceHost' => null],
            ['source' => '/source-3', 'target' => '/target-3', 'statusCode' => 302, 'enabled' => null, 'sourceHost' => null],
            ['source' => '/source-4', 'target' => '/target-4', 'statusCode' => null, 'enabled' => null, 'sourceHost' => null],
        ];

        $reader = new CsvReader();
        $fileName = __DIR__ . '/import_different_columns.csv';

        $i = 0;
        foreach ($reader->read($fileName) as $item) {
            $this->assertEquals($expected[$i]['source'], $item->getData()['source']);
            $this->assertEquals($expected[$i]['target'], $item->getData()['target']);
            $this->assertEquals($expected[$i]['statusCode'], $item->getData()['statusCode']);
            $this->assertEquals($expected[$i]['enabled'], $item->getData()['enabled']);
            $this->assertEquals($expected[$i]['sourceHost'], $item->getData()['sourceHost']);

            ++$i;
        }

        $this->assertSame(4, $i);
    }
}
