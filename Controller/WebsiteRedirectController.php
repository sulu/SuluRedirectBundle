<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Controller;

use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles redirects.
 */
class WebsiteRedirectController
{
    /**
     * Handles redirect for given redirect-route.
     *
     * @return RedirectResponse
     */
    public function redirect(Request $request, RedirectRouteInterface $redirectRoute)
    {
        if (410 === $redirectRoute->getStatusCode()) {
            throw new HttpException(410);
        }

        $queryString = http_build_query($request->query->all());

        $requestFormat = $request->getRequestFormat(null);
        $formatSuffix = $requestFormat ? ('.' . $requestFormat) : '';

        $url = [
            $redirectRoute->getTarget(),
            $formatSuffix,
            false === strpos($redirectRoute->getTarget(), '?') ? '?' : '&',
            $queryString,
        ];

        $url = trim(implode($url), '&? ');

        return new RedirectResponse($url, $redirectRoute->getStatusCode());
    }
}
