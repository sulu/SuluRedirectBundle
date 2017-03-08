<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Routing;

use Prophecy\Argument;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Sulu\Bundle\RedirectBundle\Routing\RedirectRouteProvider;
use Sulu\Component\Webspace\Analyzer\Attributes\RequestAttributes;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RedirectRouteProviderTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @var ParameterBag
     */
    private $attributesBag;

    /**
     * @var RequestAttributes
     */
    private $requestAttributes;

    protected function setUp()
    {
        $this->repository = $this->prophesize(RedirectRouteRepositoryInterface::class);

        $this->routeProvider = new RedirectRouteProvider($this->repository->reveal());

        $this->request = $this->prophesize(Request::class);
        $this->attributesBag = $this->prophesize(ParameterBag::class);
        $this->requestAttributes = $this->prophesize(RequestAttributes::class);

        $this->request->attributes = $this->attributesBag->reveal();
    }

    public function testGetRouteCollectionForRequest()
    {
        $pathInfo = '/test';
        $resourceLocator = '/test-1';
        $resourceLocatorPrefix = '/de';
        $uuid = '123-123-123';

        $this->request->getPathInfo()->willReturn($pathInfo);

        $this->attributesBag->get('_sulu', Argument::any())->willReturn($this->requestAttributes->reveal());
        $this->requestAttributes->getAttribute('resourceLocator', $pathInfo)->willReturn($resourceLocator);
        $this->requestAttributes->getAttribute('resourceLocatorPrefix', '')->willReturn($resourceLocatorPrefix);

        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->getUuid()->willReturn($uuid);
        $this->repository->findEnabledBySource($resourceLocator)->willReturn($redirectRoute->reveal());

        $result = $this->routeProvider->getRouteCollectionForRequest($this->request->reveal());
        $this->assertCount(1, $result);

        $this->assertEquals(
            [
                '_controller' => 'sulu_redirect.controller.redirect:redirect',
                'redirectRoute' => $redirectRoute->reveal(),
                'resourceLocatorPrefix' => $resourceLocatorPrefix,
            ],
            $result->get('sulu_redirect.' . $uuid)->getDefaults()
        );
    }

    public function testGetRouteCollectionForRequestNoRoute()
    {
        $pathInfo = '/test';
        $resourceLocator = '/test-1';
        $resourceLocatorPrefix = '/de';
        $uuid = '123-123-123';

        $this->request->getPathInfo()->willReturn($pathInfo);

        $this->attributesBag->get('_sulu', Argument::any())->willReturn($this->requestAttributes->reveal());
        $this->requestAttributes->getAttribute('resourceLocator', $pathInfo)->willReturn($resourceLocator);
        $this->requestAttributes->getAttribute('resourceLocatorPrefix', '')->willReturn($resourceLocatorPrefix);

        $this->repository->findEnabledBySource($resourceLocator)->willReturn(null);

        $result = $this->routeProvider->getRouteCollectionForRequest($this->request->reveal());
        $this->assertCount(0, $result);
    }

    public function testGetRouteCollectionForRequestNoRequestAttributes()
    {
        $pathInfo = '/test';
        $uuid = '123-123-123';

        $this->request->getPathInfo()->willReturn($pathInfo);

        $this->attributesBag->get('_sulu', Argument::any())->will(
            function ($arguments) {
                return $arguments[1];
            }
        );

        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->getUuid()->willReturn($uuid);
        $this->repository->findEnabledBySource($pathInfo)->willReturn($redirectRoute->reveal());

        $result = $this->routeProvider->getRouteCollectionForRequest($this->request->reveal());
        $this->assertCount(1, $result);

        $this->assertEquals(
            [
                '_controller' => 'sulu_redirect.controller.redirect:redirect',
                'redirectRoute' => $redirectRoute->reveal(),
                'resourceLocatorPrefix' => '',
            ],
            $result->get('sulu_redirect.' . $uuid)->getDefaults()
        );
    }

    public function testGetRouteCollectionForRequestNoRequestAttributesNoRoute()
    {
        $pathInfo = '/test';

        $this->request->getPathInfo()->willReturn($pathInfo);

        $this->attributesBag->get('_sulu', Argument::any())->will(
            function ($arguments) {
                return $arguments[1];
            }
        );

        $this->repository->findEnabledBySource($pathInfo)->willReturn(null);

        $result = $this->routeProvider->getRouteCollectionForRequest($this->request->reveal());
        $this->assertCount(0, $result);
    }
}
