<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\GoneSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Bundle\RedirectBundle\Exception\RedirectRouteNotUniqueException;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManagerInterface;
use Sulu\Bundle\RouteBundle\Model\RouteInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * This gone subscriber listens for removed route entities.
 *
 * @internal this is a internal listener which should not be used directly
 */
class GoneEntitySubscriber implements EventSubscriber, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var RedirectRouteManagerInterface
     */
    private $redirectRouteManager;

    public function __construct(
        RedirectRouteManagerInterface $redirectRouteManager
    ) {
        $this->redirectRouteManager = $redirectRouteManager;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        $route = $event->getObject();

        if (!$route instanceof RouteInterface) {
            return;
        }

        $redirectRoute = new RedirectRoute();
        $redirectRoute->setEnabled(true);
        $redirectRoute->setStatusCode(410);
        $redirectRoute->setSource($route->getPath());

        try {
            $this->redirectRouteManager->save($redirectRoute);
        } catch (RedirectRouteNotUniqueException $exception) {
            // do nothing when there already exists a redirect route
        }
    }
}
