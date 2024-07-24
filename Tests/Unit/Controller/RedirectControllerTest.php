<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\RedirectBundle\Controller\WebsiteRedirectController;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class RedirectControllerTest extends TestCase
{
    /**
     * @var WebsiteRedirectController
     */
    private $controller;

    /**
     * @var RedirectRouteInterface
     */
    private $redirectRoute;

    protected function setUp(): void
    {
        $this->controller = new WebsiteRedirectController();
        $this->redirectRoute = $this->prophesize(RedirectRouteInterface::class);
    }

    public function testRedirect()
    {
        $target = '/test';
        $statusCode = 301;

        $request = Request::create('http://captain-sulu.io/');

        $this->redirectRoute->getTarget()->willReturn($target);
        $this->redirectRoute->getStatusCode()->willReturn($statusCode);

        $response = $this->controller->redirect($request, $this->redirectRoute->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($target, $response->getTargetUrl());
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public function testRedirectWithQuery()
    {
        $target = '/test';
        $statusCode = 301;
        $query = ['test' => 1, 'my-parameter' => 'awesome sulu'];

        $request = Request::create('http://captain-sulu.io/');
        $request->query->set('test', $query['test']);
        $request->query->set('my-parameter', $query['my-parameter']);

        $this->redirectRoute->getTarget()->willReturn($target);
        $this->redirectRoute->getStatusCode()->willReturn($statusCode);

        $response = $this->controller->redirect($request, $this->redirectRoute->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(
            $target . '?' . http_build_query($query),
            $response->getTargetUrl()
        );
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public function testRedirectExternal()
    {
        $target = 'http://captain-sulu.io/test';
        $statusCode = 301;

        $request = Request::create('http://captain-sulu.io/');

        $this->redirectRoute->getTarget()->willReturn($target);
        $this->redirectRoute->getStatusCode()->willReturn($statusCode);

        $response = $this->controller->redirect($request, $this->redirectRoute->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(
            $target,
            $response->getTargetUrl()
        );
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public function testRedirectExternalWithQuery()
    {
        $target = 'http://captain-sulu.io/test?hello=true';
        $statusCode = 301;
        $query = ['test' => 1, 'my-parameter' => 'awesome sulu'];

        $request = Request::create('http://captain-sulu.io/');
        $request->query->set('test', $query['test']);
        $request->query->set('my-parameter', $query['my-parameter']);

        $this->redirectRoute->getTarget()->willReturn($target);
        $this->redirectRoute->getStatusCode()->willReturn($statusCode);

        $response = $this->controller->redirect($request, $this->redirectRoute->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(
            $target . '&' . http_build_query($query),
            $response->getTargetUrl()
        );
        $this->assertEquals($statusCode, $response->getStatusCode());
    }
}
