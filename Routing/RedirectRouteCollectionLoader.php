<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Routing;

use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Sulu\Route\Application\Routing\Matcher\RouteCollectionForRequestLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Loads redirect routes for the current request.
 */
final readonly class RedirectRouteCollectionLoader implements RouteCollectionForRequestLoaderInterface
{
    public function __construct(
        private RedirectRouteRepositoryInterface $redirectRouteRepository,
    ) {
    }

    public function getRouteCollectionForRequest(Request $request): RouteCollection
    {
        // server encodes the url and symfony does not encode it
        // symfony decodes this data here https://github.com/symfony/symfony/blob/v5.2.3/src/Symfony/Component/Routing/Matcher/UrlMatcher.php#L88
        $pathInfo = \rawurldecode($request->getPathInfo());
        $path = \str_replace('.' . $request->getRequestFormat(), '', $pathInfo);

        $redirectRoute = $this->redirectRouteRepository->findEnabledBySource($path, $request->getHost());

        if (!$redirectRoute) {
            return new RouteCollection();
        }

        $route = new Route(
            $pathInfo,
            [
                '_controller' => 'sulu_redirect.controller.redirect::redirect',
                'redirectRoute' => $redirectRoute,
            ],
            [],
            ['utf8' => true],
        );

        $routeCollection = new RouteCollection();
        $routeCollection->add('sulu_redirect.' . $redirectRoute->getId(), $route);

        return $routeCollection;
    }
}
