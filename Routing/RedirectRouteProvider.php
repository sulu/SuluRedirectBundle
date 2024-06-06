<?php

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
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
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
     * @var array<string, mixed>
     */
    private $defaultOptions;

    /**
     * @param array<string, mixed> $defaultOptions
     */
    public function __construct(
        RedirectRouteRepositoryInterface $redirectRouteRepository,
        array $defaultOptions = []
    ) {
        $this->redirectRouteRepository = $redirectRouteRepository;
        $this->defaultOptions = $defaultOptions;
    }

    public function getRouteCollectionForRequest(Request $request): RouteCollection
    {
        // server encodes the url and symfony does not encode it
        // symfony decodes this data here https://github.com/symfony/symfony/blob/v5.2.3/src/Symfony/Component/Routing/Matcher/UrlMatcher.php#L88
        $pathInfo = rawurldecode($request->getPathInfo());
        $path = \str_replace('.' . $request->getRequestFormat(), '', $pathInfo);
        $host = $request->getHost();

        $routeCollection = new RouteCollection();
        if (!$redirectRoute = $this->redirectRouteRepository->findEnabledBySource($path, $host)) {
            return $routeCollection;
        }

        $route = new Route(
            $pathInfo,
            [
                '_controller' => 'sulu_redirect.controller.redirect::redirect',
                'redirectRoute' => $redirectRoute,
            ],
            [],
            $this->defaultOptions
        );
        $routeCollection->add(sprintf('sulu_redirect.%s', $redirectRoute->getId()), $route);

        return $routeCollection;
    }

    public function getRouteByName($name): Route
    {
        throw new RouteNotFoundException('RedirectRouteProvider does not support getRouteByName.');
    }

    public function getRoutesByNames($names = null): iterable
    {
        return [];
    }
}
