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

use Prophecy\Argument;
use Sulu\Bundle\RedirectBundle\Import\Converter\Converter;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $repository = $this->prophesize(RedirectRouteRepositoryInterface::class);

        $converter = new Converter($repository->reveal());

        $this->assertTrue($converter->supports([Converter::SOURCE => '/source', Converter::TARGET => '/target']));
    }

    public function testConvertCreateNew()
    {
        $entity = $this->prophesize(RedirectRouteInterface::class);

        $repository = $this->prophesize(RedirectRouteRepositoryInterface::class);
        $repository->findBySource('/source')->willReturn(null);
        $repository->createNew()->willReturn($entity->reveal());

        $converter = new Converter($repository->reveal());

        $entity->setId(Argument::type('string'))->shouldBeCalled();
        $entity->setSource('/source')->shouldBeCalled();
        $entity->setTarget('/target')->shouldBeCalled();

        $result = $converter->convert([Converter::SOURCE => '/source', Converter::TARGET => '/target']);
        $this->assertInstanceOf(RedirectRouteInterface::class, $result);
        $this->assertEquals($entity->reveal(), $result);
    }

    public function testConvertUpdate()
    {
        $entity = $this->prophesize(RedirectRouteInterface::class);

        $repository = $this->prophesize(RedirectRouteRepositoryInterface::class);
        $repository->findBySource('/source')->willReturn($entity->reveal());
        $repository->createNew()->shouldNotBeCalled();

        $converter = new Converter($repository->reveal());

        $entity->setId(Argument::type('string'))->shouldNotBeCalled();
        $entity->setSource('/source')->shouldBeCalled();
        $entity->setTarget('/target')->shouldBeCalled();

        $result = $converter->convert([Converter::SOURCE => '/source', Converter::TARGET => '/target']);
        $this->assertInstanceOf(RedirectRouteInterface::class, $result);
        $this->assertEquals($entity->reveal(), $result);
    }
}
