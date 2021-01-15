<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Routing;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Sulu\Bundle\RedirectBundle\Routing\RedirectRouteProvider;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class RedirectRouteProviderTest extends TestCase
{
    /**
     * @var RedirectRouteRepositoryInterface
     */
    private $repository;

    /**
     * @var RouteProviderInterface
     */
    private $routeProvider;

    /**
     * @var Request
     */
    private $request;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(RedirectRouteRepositoryInterface::class);

        $this->routeProvider = new RedirectRouteProvider($this->repository->reveal());

        $this->request = $this->prophesize(Request::class);
    }

    public function testGetRouteCollectionForRequest()
    {
        $pathInfo = '/test';
        $host = null;
        $uuid = '123-123-123';

        $this->request->getPathInfo()->willReturn($pathInfo);
        $this->request->getHost()->willReturn($host);

        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->getId()->willReturn($uuid);
        $redirectRoute->getSource()->willReturn($pathInfo);
        $redirectRoute->getSourceHost()->willReturn($host);
        $this->repository->findEnabledBySource($pathInfo, $host)->willReturn($redirectRoute->reveal());

        $result = $this->routeProvider->getRouteCollectionForRequest($this->request->reveal());
        $this->assertCount(1, $result);

        $this->assertEquals(
            [
                '_controller' => 'sulu_redirect.controller.redirect:redirect',
                'redirectRoute' => $redirectRoute->reveal(),
            ],
            $result->get('sulu_redirect.' . $uuid)->getDefaults()
        );
    }

    public function testGetRouteCollectionForRequestEncodedPathInfo()
    {
        $pathInfo = '/käße';
        $host = null;
        $uuid = '123-123-123';

        $this->request->getPathInfo()->willReturn(rawurlencode($pathInfo));
        $this->request->getHost()->willReturn($host);

        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->getId()->willReturn($uuid);
        $redirectRoute->getSource()->willReturn($pathInfo);
        $redirectRoute->getSourceHost()->willReturn($host);
        $this->repository->findEnabledBySource($pathInfo, $host)->willReturn($redirectRoute->reveal());

        $result = $this->routeProvider->getRouteCollectionForRequest($this->request->reveal());
        $this->assertCount(1, $result);

        $this->assertEquals(
            [
                '_controller' => 'sulu_redirect.controller.redirect:redirect',
                'redirectRoute' => $redirectRoute->reveal(),
            ],
            $result->get('sulu_redirect.' . $uuid)->getDefaults()
        );
    }

    public function testGetRouteCollectionForRequestNoRoute()
    {
        $pathInfo = '/test';
        $host = null;

        $this->request->getPathInfo()->willReturn($pathInfo);
        $this->request->getHost()->willReturn($host);

        $this->repository->findEnabledBySource($pathInfo, $host)->willReturn(null);

        $result = $this->routeProvider->getRouteCollectionForRequest($this->request->reveal());
        $this->assertCount(0, $result);
    }
}
