<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Controller;

use Sulu\Bundle\RedirectBundle\Controller\RedirectController;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class RedirectControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RedirectController
     */
    private $controller;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ParameterBag
     */
    private $queryBag;

    /**
     * @var RedirectRouteInterface
     */
    private $redirectRoute;

    /**
     * @var string
     */
    protected $schemeAndHost = 'http://sulu.io';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->controller = new RedirectController();

        $this->request = $this->prophesize(Request::class);
        $this->queryBag = $this->prophesize(ParameterBag::class);
        $this->redirectRoute = $this->prophesize(RedirectRouteInterface::class);

        $this->request->getSchemeAndHttpHost()->willReturn($this->schemeAndHost);
        $this->request->reveal()->query = $this->queryBag->reveal();
    }

    public function testRedirect()
    {
        $target = '/test';
        $statusCode = 301;

        $this->queryBag->all()->willReturn([]);

        $this->redirectRoute->getTarget()->willReturn($target);
        $this->redirectRoute->getStatusCode()->willReturn($statusCode);

        $response = $this->controller->redirect($this->request->reveal(), $this->redirectRoute->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($this->schemeAndHost . $target, $response->getTargetUrl());
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public function testRedirectWithQuery()
    {
        $target = '/test';
        $statusCode = 301;
        $query = ['test' => 1, 'my-parameter' => 'awesome sulu'];

        $this->queryBag->all()->willReturn($query);

        $this->redirectRoute->getTarget()->willReturn($target);
        $this->redirectRoute->getStatusCode()->willReturn($statusCode);

        $response = $this->controller->redirect($this->request->reveal(), $this->redirectRoute->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(
            $this->schemeAndHost . $target . '?' . http_build_query($query),
            $response->getTargetUrl()
        );
        $this->assertEquals($statusCode, $response->getStatusCode());
    }
}
