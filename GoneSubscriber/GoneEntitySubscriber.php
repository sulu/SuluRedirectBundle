<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\GoneSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteNotUniqueException;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
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
     * @return RedirectRouteManager
     */
    public function getRedirectRouteManager()
    {
        return $this->container->get('sulu_redirect.redirect_route_manager');
    }

    /**
     * @return RouteRepositoryInterface
     */
    public function getRouteRepository()
    {
        return $this->container->get('sulu.repository.route');
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove
        ];
    }

    public function preRemove(LifecycleEventArgs $event)
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
            $this->getRedirectRouteManager()->save($redirectRoute);
        } catch (RedirectRouteNotUniqueException $exception) {
            // do nothing when there already exists a redirect route
        }
    }
}