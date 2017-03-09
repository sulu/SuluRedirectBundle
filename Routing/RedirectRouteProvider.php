<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Routing;

use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides routes which was managed in this bundle.
 */
class RedirectRouteProvider implements RouteProviderInterface
{
    /**
     * @var RedirectRouteRepositoryInterface
     */
    private $redirectRouteRepository;

    /**
     * @param RedirectRouteRepositoryInterface $redirectRouteRepository
     */
    public function __construct(RedirectRouteRepositoryInterface $redirectRouteRepository)
    {
        $this->redirectRouteRepository = $redirectRouteRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        $routeCollection = new RouteCollection();
        if (!$redirectRoute = $this->redirectRouteRepository->findEnabledBySource($request->getPathInfo())) {
            return $routeCollection;
        }

        $route = new Route(
            $request->getPathInfo(),
            [
                '_controller' => 'sulu_redirect.controller.redirect:redirect',
                'redirectRoute' => $redirectRoute,
            ]
        );
        $routeCollection->add(sprintf('sulu_redirect.%s', $redirectRoute->getId()), $route);

        return $routeCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteByName($name)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutesByNames($names)
    {
        return [];
    }
}
