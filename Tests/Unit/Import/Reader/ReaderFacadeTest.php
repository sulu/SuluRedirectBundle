<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Import\Reader;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\RedirectBundle\Import\Converter\Converter;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderFacade;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderInterface;

class ReaderFacadeTest extends TestCase
{
    /**
     * @var string
     */
    private $fileName;

    protected function setUp(): void
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

        $readerFacade = new ReaderFacade(
            array_map(
                function(ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertTrue($readerFacade->supports($this->fileName));
    }

    public function testSupportsNoSupportedConverter()
    {
        $converters = [
            $this->prophesize(ReaderInterface::class),
            $this->prophesize(ReaderInterface::class),
        ];

        $converters[0]->supports($this->fileName)->willReturn(false);
        $converters[1]->supports($this->fileName)->willReturn(false);

        $readerFacade = new ReaderFacade(
            array_map(
                function(ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertFalse($readerFacade->supports($this->fileName));
    }

    public function testSupportsNoConverter()
    {
        $data = ['title' => 'Test-Title'];

        $readerFacade = new ReaderFacade();

        $this->assertFalse($readerFacade->supports($data));
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

        $readerFacade = new ReaderFacade(
            array_map(
                function(ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertEquals($data, $readerFacade->read($this->fileName));
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

        $readerFacade = new ReaderFacade(
            array_map(
                function(ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertNull($readerFacade->read($data));
    }

    public function testConvertNoConverter()
    {
        $data = ['title' => 'Test-Title'];

        $readerFacade = new ReaderFacade();

        $this->assertNull($readerFacade->read($data));
    }
}
