<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Controller;

use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles redirects.
 */
class WebsiteRedirectController
{
    /**
     * Handles redirect for given redirect-route.
     *
     * @param Request $request
     * @param RedirectRouteInterface $redirectRoute
     *
     * @return RedirectResponse
     */
    public function redirect(Request $request, RedirectRouteInterface $redirectRoute)
    {
        $queryString = http_build_query($request->query->all());

        $url = [
            $redirectRoute->getTarget(),
            false === strpos($redirectRoute->getTarget(), '?') ? '?' : '&',
            $queryString,
        ];

        $url = trim(implode($url), '&? ');

        return new RedirectResponse($url, $redirectRoute->getStatusCode());
    }
}
