<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Import\Converter;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;

class RedirectRouteTest extends TestCase
{
    public function testId()
    {
        $route = new RedirectRoute();

        $this->assertSame($route, $route->setId('123-123-123-123'));
        $this->assertSame('123-123-123-123', $route->getId());
    }

    public function testEnabled()
    {
        $route = new RedirectRoute();

        $this->assertSame(true, $route->isEnabled());
        $this->assertSame($route, $route->setEnabled(false));
        $this->assertSame(false, $route->isEnabled());
    }

    public function testStatusCode()
    {
        $route = new RedirectRoute();

        $this->assertSame(301, $route->getStatusCode());
        $this->assertSame($route, $route->setStatusCode(410));
        $this->assertSame(410, $route->getStatusCode());
    }

    public function testSource()
    {
        $route = new RedirectRoute();

        $this->assertSame($route, $route->setSource('/redirect-source'));
        $this->assertSame('/redirect-source', $route->getSource());

        $this->assertSame($route, $route->setSource('missing-leading-slash'));
        $this->assertSame('/missing-leading-slash', $route->getSource());

        $this->assertSame($route, $route->setSource('/UPPERCASE-SOURCE'));
        $this->assertSame('/uppercase-source', $route->getSource());
    }

    public function testSourceHost()
    {
        $route = new RedirectRoute();

        $this->assertSame($route, $route->setSourceHost(null));
        $this->assertSame(null, $route->getSourceHost());

        $this->assertSame($route, $route->setSourceHost('sulu.io'));
        $this->assertSame('sulu.io', $route->getSourceHost());

        $this->assertSame($route, $route->setSourceHost('SULU.IO'));
        $this->assertSame('sulu.io', $route->getSourceHost());
    }

    public function testTarget()
    {
        $route = new RedirectRoute();

        $this->assertSame($route, $route->setTarget('target-url'));
        $this->assertSame('target-url', $route->getTarget());

        $this->assertSame($route, $route->setTarget('UPPERCASE-TARGET'));
        $this->assertSame('uppercase-target', $route->getTarget());
    }
}
