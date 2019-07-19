<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Manager;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManagerInterface;
use Sulu\Bundle\RedirectBundle\Manager\Exception\RedirectRouteNotUniqueException;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;

class RedirectRouteManagerTest extends TestCase
{
    /**
     * @var RedirectRouteRepositoryInterface
     */
    private $repository;

    /**
     * @var RedirectRouteManagerInterface
     */
    private $manager;

    protected function setUp()
    {
        $this->repository = $this->prophesize(RedirectRouteRepositoryInterface::class);

        $this->manager = new RedirectRouteManager($this->repository->reveal());
    }

    public function testSave()
    {
        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->setId(Argument::any())->shouldBeCalled();
        $redirectRoute->setSource('/test')->shouldBeCalled();
        $redirectRoute->setEnabled(true)->shouldBeCalled();
        $redirectRoute->setTarget('/test2')->shouldBeCalled();
        $redirectRoute->setStatusCode(301)->shouldBeCalled();
        $redirectRoute->getStatusCode()->willReturn(301);

        $this->repository->findBySource('/test')->willReturn(null);
        $this->repository->createNew()->willReturn($redirectRoute->reveal());
        $this->repository->persist($redirectRoute->reveal())->shouldBeCalled();

        $this->manager->saveByData(['source' => '/test', 'target' => '/test2', 'enabled' => true, 'statusCode' => 301]);
    }

    public function testSave410()
    {
        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->setTarget('')->shouldBeCalled();
        $redirectRoute->setSource('/test410')->shouldBeCalled();
        $redirectRoute->setId(Argument::any())->shouldBeCalled();
        $redirectRoute->setEnabled(true)->shouldBeCalled();
        $redirectRoute->setStatusCode(410)->shouldBeCalled();
        $redirectRoute->getStatusCode()->willReturn(410);

        $this->repository->findBySource('/test410')->willReturn(null);
        $this->repository->createNew()->willReturn($redirectRoute->reveal());
        $this->repository->persist($redirectRoute->reveal())->shouldBeCalled();

        $this->manager->saveByData(['source' => '/test410', 'target' => '', 'enabled' => true, 'statusCode' => 410]);
    }

    public function testSaveAlreadyExists()
    {
        $this->expectException(RedirectRouteNotUniqueException::class);

        $otherRoute = $this->prophesize(RedirectRouteInterface::class);
        $otherRoute->getId()->willReturn('123-123-123');

        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);

        $this->repository->findBySource('/test')->willReturn($otherRoute->reveal());
        $this->repository->createNew()->willReturn($redirectRoute->reveal());

        $this->manager->saveByData(['source' => '/test', 'target' => '/test2', 'enabled' => true, 'statusCode' => 301]);

        $this->repository->persist($redirectRoute->reveal())->shouldNotBeCalled();
    }

    public function testSaveSameEntity()
    {
        $otherRoute = $this->prophesize(RedirectRouteInterface::class);
        $otherRoute->getId()->willReturn('123-123-123');

        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->getId()->willReturn('123-123-123');
        $redirectRoute->setSource('/test')->shouldBeCalled();
        $redirectRoute->setEnabled(true)->shouldBeCalled();
        $redirectRoute->setTarget('/test2')->shouldBeCalled();
        $redirectRoute->setStatusCode(301)->shouldBeCalled();
        $redirectRoute->getStatusCode()->willReturn(301);


        $this->repository->findById('123-123-123')->willReturn($redirectRoute->reveal());
        $this->repository->findBySource('/test')->willReturn($otherRoute->reveal());
        $this->repository->persist($redirectRoute->reveal())->shouldBeCalled();

        $this->manager->saveByData(['source' => '/test', 'target' => '/test2', 'enabled' => true, 'statusCode' => 301, 'id' => '123-123-123']);
    }

    public function testDelete()
    {
        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);

        $this->manager->delete($redirectRoute->reveal());

        $this->repository->remove($redirectRoute->reveal())->shouldBeCalled();
    }
}
