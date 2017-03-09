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

use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManagerInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;

class RedirectRouteManagerTest extends \PHPUnit_Framework_TestCase
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

        $this->manager->save($redirectRoute->reveal());

        $this->repository->persist($redirectRoute->reveal())->shouldBeCalled();
    }
}
