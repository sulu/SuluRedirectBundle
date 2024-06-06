<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Manager;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\RedirectBundle\Exception\RedirectRouteNotUniqueException;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManagerInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;

class RedirectRouteManagerTest extends TestCase
{
    /**
     * @var ObjectProphecy<RedirectRouteRepositoryInterface>
     */
    private $repository;

    /**
     * @var RedirectRouteManagerInterface
     */
    private $manager;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(RedirectRouteRepositoryInterface::class);

        $this->manager = new RedirectRouteManager($this->repository->reveal());
    }

    public function testSave()
    {
        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->setId(Argument::any())->shouldBeCalled();
        $redirectRoute->setSource('/test')->shouldBeCalled();
        $redirectRoute->setSourceHost(null)->shouldBeCalled();
        $redirectRoute->setEnabled(Argument::any())->shouldNotBeCalled();
        $redirectRoute->setTarget('/test2')->shouldBeCalled();
        $redirectRoute->setStatusCode(301)->shouldBeCalled();
        $redirectRoute->getStatusCode()->willReturn(301);

        $this->repository->findBySource('/test', null)->willReturn(null);
        $this->repository->createNew()->willReturn($redirectRoute->reveal());
        $this->repository->persist($redirectRoute->reveal())->shouldBeCalled();

        $this->manager->saveByData(['source' => '/test', 'sourceHost' => null, 'target' => '/test2', 'statusCode' => 301]);
    }

    public function testSave410()
    {
        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->setTarget('')->shouldBeCalled();
        $redirectRoute->setSource('/test410')->shouldBeCalled();
        $redirectRoute->setSourceHost(null)->shouldBeCalled();
        $redirectRoute->setId(Argument::any())->shouldBeCalled();
        $redirectRoute->setEnabled(Argument::any())->shouldNotBeCalled();
        $redirectRoute->setStatusCode(410)->shouldBeCalled();
        $redirectRoute->getStatusCode()->willReturn(410);

        $this->repository->findBySource('/test410', null)->willReturn(null);
        $this->repository->createNew()->willReturn($redirectRoute->reveal());
        $this->repository->persist($redirectRoute->reveal())->shouldBeCalled();

        $this->manager->saveByData(['source' => '/test410', 'sourceHost' => null, 'target' => '', 'statusCode' => 410]);
    }

    public function testSaveAlreadyExists()
    {
        $this->expectException(RedirectRouteNotUniqueException::class);

        $otherRoute = $this->prophesize(RedirectRouteInterface::class);
        $otherRoute->getId()->willReturn('123-123-123');
        $otherRoute->getSourceHost()->willReturn('www.example.com');

        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->setId(Argument::any())->shouldBeCalled();
        $redirectRoute->setSource('/test')->shouldBeCalled();
        $redirectRoute->setSourceHost('www.example.com')->shouldBeCalled();
        $redirectRoute->setEnabled(Argument::any())->shouldNotBeCalled();
        $redirectRoute->setTarget('/test2')->shouldBeCalled();
        $redirectRoute->setStatusCode(301)->shouldBeCalled();
        $redirectRoute->getStatusCode()->willReturn(301);

        $redirectRoute->getId()->willReturn('234-234-234');
        $redirectRoute->getSourceHost()->willReturn('www.example.com');

        $this->repository->findBySource('/test', 'www.example.com')->willReturn($otherRoute->reveal());
        $this->repository->createNew()->willReturn($redirectRoute->reveal());

        $this->manager->saveByData(['source' => '/test', 'sourceHost' => 'www.example.com', 'target' => '/test2', 'statusCode' => 301]);

        $this->repository->persist($redirectRoute->reveal())->shouldNotBeCalled();
    }

    public function testSaveSameEntity()
    {
        $otherRoute = $this->prophesize(RedirectRouteInterface::class);
        $otherRoute->getId()->willReturn('123-123-123');

        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->getId()->willReturn('123-123-123');
        $redirectRoute->setSource('/test')->shouldBeCalled();
        $redirectRoute->setSourceHost('www.example.com')->shouldBeCalled();
        $redirectRoute->setEnabled(Argument::any())->shouldNotBeCalled();
        $redirectRoute->setTarget('/test2')->shouldBeCalled();
        $redirectRoute->setStatusCode(301)->shouldBeCalled();
        $redirectRoute->getStatusCode()->willReturn(301);

        $this->repository->findById('123-123-123')->willReturn($redirectRoute->reveal());
        $this->repository->findBySource('/test', 'www.example.com')->willReturn($otherRoute->reveal());
        $this->repository->persist($redirectRoute->reveal())->shouldBeCalled();

        $this->manager->saveByData(['source' => '/test', 'sourceHost' => 'www.example.com', 'target' => '/test2', 'statusCode' => 301, 'id' => '123-123-123']);
    }

    public function testDelete()
    {
        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);

        $this->manager->delete($redirectRoute->reveal());

        $this->repository->remove($redirectRoute->reveal())->shouldBeCalled();
    }
}
