<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\GoneSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Bundle\RedirectBundle\GoneSubscriber\GoneEntitySubscriber;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Sulu\Bundle\RouteBundle\Model\RouteInterface;

class GoneEntitySubscriberTest extends TestCase
{
    /**
     * @var GoneEntitySubscriber
     */
    private $goneEntitySubscriber;

    /**
     * @var ObjectProphecy<LifecycleEventArgs>
     */
    private $event;

    /**
     * @var ObjectProphecy<RouteInterface>
     */
    private $object;

    /**
     * @var ObjectProphecy<RedirectRouteManager>
     */
    private $redirectRouteManager;

    protected function setUp(): void
    {
        $this->object = $this->prophesize(RouteInterface::class);
        $this->object->getPath()->willReturn('/test/123');

        $this->event = $this->prophesize(LifecycleEventArgs::class);
        $this->event->getObject()->willReturn($this->object->reveal());

        $this->redirectRouteManager = $this->prophesize(RedirectRouteManager::class);
        $this->redirectRouteManager->save(Argument::that(function($object) {
            $this->assertEquals($object->getSource(), '/test/123');

            return true;
        }))->shouldBeCalledTimes(1);

        $this->goneEntitySubscriber = new GoneEntitySubscriber($this->redirectRouteManager->reveal());
    }

    public function testPreRemoveWithWrongObject()
    {
        $wrongObject = $this->prophesize(RedirectRoute::class);

        $event = $this->prophesize(LifecycleEventArgs::class);
        $event->getObject()->willReturn($wrongObject->reveal());

        $this->redirectRouteManager->save()->shouldNotBeCalled();

        $this->goneEntitySubscriber->preRemove($this->event->reveal());
    }

    public function testPreRemove()
    {
        $this->goneEntitySubscriber->preRemove($this->event->reveal());
    }
}
