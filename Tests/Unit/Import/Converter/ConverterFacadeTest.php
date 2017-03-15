<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Import\Converter;

use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\RedirectBundle\Import\Converter\ConverterFacade;
use Sulu\Bundle\RedirectBundle\Import\Converter\ConverterInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;

class ConverterFacadeTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $data = ['title' => 'Test-Title'];

        $converters = [
            $this->prophesize(ConverterInterface::class),
            $this->prophesize(ConverterInterface::class),
        ];

        $converters[0]->supports($data)->willReturn(false);
        $converters[1]->supports($data)->willReturn(true);

        $converterFacade = new ConverterFacade(
            array_map(
                function (ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertTrue($converterFacade->supports($data));
    }

    public function testSupportsNoSupportedConverter()
    {
        $data = ['title' => 'Test-Title'];

        $converters = [
            $this->prophesize(ConverterInterface::class),
            $this->prophesize(ConverterInterface::class),
        ];

        $converters[0]->supports($data)->willReturn(false);
        $converters[1]->supports($data)->willReturn(false);

        $converterFacade = new ConverterFacade(
            array_map(
                function (ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertFalse($converterFacade->supports($data));
    }

    public function testSupportsNoConverter()
    {
        $data = ['title' => 'Test-Title'];

        $converterFacade = new ConverterFacade();

        $this->assertFalse($converterFacade->supports($data));
    }

    public function testConvert()
    {
        $data = ['title' => 'Test-Title'];

        $converters = [
            $this->prophesize(ConverterInterface::class),
            $this->prophesize(ConverterInterface::class),
        ];

        $converters[0]->supports($data)->willReturn(false);
        $converters[0]->convert($data)->shouldNotBeCalled();
        $converters[1]->supports($data)->willReturn(true);
        $converters[1]->convert($data)
            ->willReturn($this->prophesize(RedirectRouteInterface::class)->reveal())
            ->shouldBeCalled();

        $converterFacade = new ConverterFacade(
            array_map(
                function (ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertInstanceOf(RedirectRouteInterface::class, $converterFacade->convert($data));
    }

    public function testConvertNotSupported()
    {
        $data = ['title' => 'Test-Title'];

        $converters = [
            $this->prophesize(ConverterInterface::class),
            $this->prophesize(ConverterInterface::class),
        ];

        $converters[0]->supports($data)->willReturn(false);
        $converters[0]->convert($data)->shouldNotBeCalled();
        $converters[1]->supports($data)->willReturn(false);
        $converters[1]->convert($data)->shouldNotBeCalled();

        $converterFacade = new ConverterFacade(
            array_map(
                function (ObjectProphecy $converter) {
                    return $converter->reveal();
                },
                $converters
            )
        );

        $this->assertNull($converterFacade->convert($data));
    }

    public function testConvertNoConverter()
    {
        $data = ['title' => 'Test-Title'];

        $converterFacade = new ConverterFacade();

        $this->assertNull($converterFacade->convert($data));
    }
}
