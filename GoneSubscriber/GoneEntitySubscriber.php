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
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Sulu\Bundle\RouteBundle\Model\RouteInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * This gone subscriber listens for removed route entities.
 */
class GoneEntitySubscriber implements EventSubscriber, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        $route = $event->getObject();

        $routeManager = $this->getRedirectRouteManager();
        if (!$route instanceof RouteInterface || null === $routeManager) {
            return;
        }

        $redirectRoute = new RedirectRoute();
        $redirectRoute->setEnabled(true);
        $redirectRoute->setStatusCode(410);
        $redirectRoute->setSource($route->getPath());

        try {
            $routeManager->save($redirectRoute);
        } catch (RedirectRouteNotUniqueException $exception) {
            // do nothing when there already exists a redirect route
        }
    }

    /**
     * @return RedirectRouteManager|null
     */
    private function getRedirectRouteManager()
    {
        if (null === $this->container) {
            return null;
        }

        return $this->container->get('sulu_redirect.redirect_route_manager');
    }
}
