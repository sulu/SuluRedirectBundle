<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Import\Reader;

use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\RedirectBundle\Import\Converter\Converter;
use Sulu\Bundle\RedirectBundle\Import\Reader\ChainReader;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderInterface;

class ChainReaderTest extends \PHPUnit_Framework_TestCase
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
        $this->fileName = '/import.csv';
    }

    public function testSupports()
    {
        $converters = [
            $this->prophesize(ReaderInterface::class),
            $this->prophesize(ReaderInterface::class),
        ];

        $converters[0]->supports($this->fileName)->willReturn(false);
        $converters[1]->supports($this->fileName)->willReturn(true);

        $chainConverter = new ChainReader(
            array_map(
                function (ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertTrue($chainConverter->supports($this->fileName));
    }

    public function testSupportsNoSupportedConverter()
    {
        $converters = [
            $this->prophesize(ReaderInterface::class),
            $this->prophesize(ReaderInterface::class),
        ];

        $converters[0]->supports($this->fileName)->willReturn(false);
        $converters[1]->supports($this->fileName)->willReturn(false);

        $chainConverter = new ChainReader(
            array_map(
                function (ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertFalse($chainConverter->supports($this->fileName));
    }

    public function testSupportsNoConverter()
    {
        $data = ['title' => 'Test-Title'];

        $chainConverter = new ChainReader();

        $this->assertFalse($chainConverter->supports($data));
    }

    public function testRead()
    {
        $data = [[Converter::SOURCE => '/source', Converter::TARGET => '/target']];

        $converters = [
            $this->prophesize(ReaderInterface::class),
            $this->prophesize(ReaderInterface::class),
        ];

        $converters[0]->supports($this->fileName)->willReturn(false);
        $converters[0]->read($this->fileName)->shouldNotBeCalled();
        $converters[1]->supports($this->fileName)->willReturn(true);
        $converters[1]->read($this->fileName)->willReturn($data)->shouldBeCalled();

        $chainReader = new ChainReader(
            array_map(
                function (ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertEquals($data, $chainReader->read($this->fileName));
    }

    public function testConvertNotSupported()
    {
        $data = ['title' => 'Test-Title'];

        $converters = [
            $this->prophesize(ReaderInterface::class),
            $this->prophesize(ReaderInterface::class),
        ];

        $converters[0]->supports($data)->willReturn(false);
        $converters[0]->read($data)->shouldNotBeCalled();
        $converters[1]->supports($data)->willReturn(false);
        $converters[1]->read($data)->shouldNotBeCalled();

        $chainConverter = new ChainReader(
            array_map(
                function (ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertNull($chainConverter->read($data));
    }

    public function testConvertNoConverter()
    {
        $data = ['title' => 'Test-Title'];

        $chainConverter = new ChainReader();

        $this->assertNull($chainConverter->read($data));
    }
}
