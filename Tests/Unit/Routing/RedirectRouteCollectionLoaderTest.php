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
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Sulu\Bundle\RedirectBundle\Routing\RedirectRouteCollectionLoader;
use Sulu\Route\Application\Routing\Matcher\RouteCollectionForRequestLoaderInterface;
use Symfony\Component\HttpFoundation\Request;

class RedirectRouteCollectionLoaderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var RedirectRouteRepositoryInterface
     */
    private $repository;

    /**
     * @var RouteCollectionForRequestLoaderInterface
     */
    private $loader;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(RedirectRouteRepositoryInterface::class);

        $this->loader = new RedirectRouteCollectionLoader($this->repository->reveal());
    }

    public function testGetRouteCollectionForRequest()
    {
        $pathInfo = '/test';
        $host = null;
        $uuid = '123-123-123';

        $request = Request::create('/test');

        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->getId()->willReturn($uuid);
        $redirectRoute->getSource()->willReturn($pathInfo);
        $redirectRoute->getSourceHost()->willReturn($host);
        $this->repository->findEnabledBySource($pathInfo, 'localhost')->willReturn($redirectRoute->reveal());

        $result = $this->loader->getRouteCollectionForRequest($request);
        $this->assertCount(1, $result);

        $this->assertEquals(
            [
                '_controller' => 'sulu_redirect.controller.redirect::redirect',
                'redirectRoute' => $redirectRoute->reveal(),
            ],
            $result->get('sulu_redirect.' . $uuid)->getDefaults()
        );

        $this->assertArrayHasKey('utf8', $result->get('sulu_redirect.' . $uuid)->getOptions());
        $this->assertSame(true, $result->get('sulu_redirect.' . $uuid)->getOptions()['utf8']);
    }

    public function testGetRouteCollectionForRequestEncodedPathInfo()
    {
        $pathInfo = '/käße';
        $host = null;
        $uuid = '123-123-123';

        // Browsers URL-encode non-ASCII characters: /käße -> /k%C3%A4%C3%9Fe
        $request = Request::create('/k%C3%A4%C3%9Fe');

        $redirectRoute = $this->prophesize(RedirectRouteInterface::class);
        $redirectRoute->getId()->willReturn($uuid);
        $redirectRoute->getSource()->willReturn($pathInfo);
        $redirectRoute->getSourceHost()->willReturn($host);
        $this->repository->findEnabledBySource($pathInfo, 'localhost')->willReturn($redirectRoute->reveal());

        $result = $this->loader->getRouteCollectionForRequest($request);
        $this->assertCount(1, $result);

        $this->assertEquals(
            [
                '_controller' => 'sulu_redirect.controller.redirect::redirect',
                'redirectRoute' => $redirectRoute->reveal(),
            ],
            $result->get('sulu_redirect.' . $uuid)->getDefaults()
        );
    }

    public function testGetRouteCollectionForRequestNoRoute()
    {
        $pathInfo = '/test';

        $request = Request::create('/test');

        $this->repository->findEnabledBySource($pathInfo, 'localhost')->willReturn(null);

        $result = $this->loader->getRouteCollectionForRequest($request);
        $this->assertCount(0, $result);
    }
}
