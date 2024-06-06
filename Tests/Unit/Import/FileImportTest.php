<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Import;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\RedirectBundle\Import\Converter\Converter;
use Sulu\Bundle\RedirectBundle\Import\Converter\ConverterInterface;
use Sulu\Bundle\RedirectBundle\Import\Converter\ConverterNotFoundException;
use Sulu\Bundle\RedirectBundle\Import\FileImport;
use Sulu\Bundle\RedirectBundle\Import\FileImportInterface;
use Sulu\Bundle\RedirectBundle\Import\ImportException;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderInterface;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderItem;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderNotFoundException;
use Sulu\Bundle\RedirectBundle\Import\Writer\WriterInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;

class FileImportTest extends TestCase
{
    /**
     * @var ObjectProphecy<ReaderInterface>
     */
    private $reader;

    /**
     * @var ObjectProphecy<ConverterInterface>
     */
    private $converter;

    /**
     * @var ObjectProphecy<WriterInterface>
     */
    private $writer;

    /**
     * @var FileImportInterface
     */
    private $import;

    /**
     * @var string
     */
    private $fileName;

    protected function setUp(): void
    {
        $this->reader = $this->prophesize(ReaderInterface::class);
        $this->converter = $this->prophesize(ConverterInterface::class);
        $this->writer = $this->prophesize(WriterInterface::class);

        $this->import = new FileImport($this->reader->reveal(), $this->converter->reveal(), $this->writer->reveal());

        $this->fileName = '/test.csv';
    }

    public function testImport()
    {
        $items = [
            new ReaderItem(1, '', [Converter::SOURCE => '/source-1', Converter::TARGET => '/target-1']),
            new ReaderItem(2, '', [Converter::SOURCE => '/source-2', Converter::TARGET => '/target-2']),
        ];
        $entities = [
            $this->prophesize(RedirectRouteInterface::class),
            $this->prophesize(RedirectRouteInterface::class),
        ];

        $this->reader->supports($this->fileName)->willReturn(true);
        $this->reader->read($this->fileName)->willReturn($items);

        $this->converter->supports($items[0]->getData())->willReturn(true);
        $this->converter->convert($items[0]->getData())->willReturn($entities[0]->reveal());
        $this->converter->supports($items[1]->getData())->willReturn(true);
        $this->converter->convert($items[1]->getData())->willReturn($entities[1]->reveal());

        $this->writer->write($entities[0])->shouldBeCalled();
        $this->writer->write($entities[1])->shouldBeCalled();
        $this->writer->finalize()->shouldBeCalled();

        foreach ($this->import->import($this->fileName) as $item) {
            $expected = $entities[$item->getLineNumber() - 1];

            $this->assertNull($item->getException());
            $this->assertEquals($expected->reveal(), $item->getData());
        }
    }

    public function testImportNoReaderSupports()
    {
        $this->expectException(ReaderNotFoundException::class);

        $this->reader->supports($this->fileName)->willReturn(false);

        foreach ($this->import->import($this->fileName) as $item) {
            // no item will be returned
        }
    }

    public function testImportExceptionItem()
    {
        $this->reader->supports($this->fileName)->willReturn(true);
        $this->reader->read($this->fileName)->willReturn(
            [
                new ReaderItem(1, '', [], $this->prophesize(ImportException::class)->reveal()),
            ]
        );

        foreach ($this->import->import($this->fileName) as $item) {
            $this->assertInstanceOf(ImportException::class, $item->getException());
            $this->assertEquals(1, $item->getLineNumber());
            $this->assertNull($item->getData());
        }
    }

    public function testImportNoSupportedConverter()
    {
        $item = new ReaderItem(1, '', [Converter::SOURCE => '/source-1', Converter::TARGET => '/target-1']);

        $this->reader->supports($this->fileName)->willReturn(true);
        $this->reader->read($this->fileName)->willReturn([$item]);

        $this->converter->supports($item->getData())->willReturn(false);

        $this->writer->write(Argument::any())->shouldNotBeCalled();
        $this->writer->finalize()->shouldBeCalled();

        foreach ($this->import->import($this->fileName) as $item) {
            $this->assertInstanceOf(ConverterNotFoundException::class, $item->getException());
            $this->assertEquals(1, $item->getLineNumber());
            $this->assertNull($item->getData());
        }
    }
}
