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
        // server encodes the url and symfony does not encode it
        // symfony decodes this data here https://github.com/symfony/symfony/blob/3.3/src/Symfony/Component/Routing/Matcher/UrlMatcher.php#L91
        $pathInfo = rawurldecode($request->getPathInfo());
        $host = $request->getHttpHost();

        $routeCollection = new RouteCollection();
        if (!$redirectRoute = $this->redirectRouteRepository->findEnabledBySource($pathInfo, $host)) {
            return $routeCollection;
        }

        $route = new Route(
            $redirectRoute->getSource(),
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
