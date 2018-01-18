<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\GoneSubscriber;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Document\BasePageDocument;
use Sulu\Bundle\DocumentManagerBundle\Bridge\DocumentInspector;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Bundle\RedirectBundle\GoneSubscriber\GoneDocumentSubscriber;
use Sulu\Bundle\RedirectBundle\GoneSubscriber\GoneEntitySubscriber;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Sulu\Bundle\RouteBundle\Model\RouteInterface;
use Sulu\Component\Content\Types\ResourceLocator\ResourceLocatorInformation;
use Sulu\Component\Content\Types\ResourceLocator\Strategy\ResourceLocatorStrategyInterface;
use Sulu\Component\Content\Types\ResourceLocator\Strategy\ResourceLocatorStrategyPool;
use Sulu\Component\DocumentManager\Event\RemoveEvent;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceManager;
use Sulu\Component\Webspace\Webspace;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GoneEntitySubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GoneEntitySubscriber
     */
    private $goneEntitySubscriber;

    /**
     * @var LifecycleEventArgs
     */
    private $event;

    /**
     * @var RouteInterface
     */
    private $object;

    /**
     * @var RedirectRouteManager
     */
    private $redirectRouteManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    protected function setUp()
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

        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get('sulu_redirect.redirect_route_manager')->willReturn($this->redirectRouteManager->reveal());

        $this->goneEntitySubscriber = new GoneEntitySubscriber();
        $this->goneEntitySubscriber->setContainer($this->container->reveal());
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
