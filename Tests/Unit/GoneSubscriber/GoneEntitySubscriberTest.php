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

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\GoneSubscriber;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\RedirectBundle\GoneSubscriber\GoneEntitySubscriber;
use Sulu\Page\Domain\Model\Page;
use Sulu\Route\Domain\Model\Route;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;

class GoneEntitySubscriberTest extends TestCase
{
    use ProphecyTrait;

    private GoneEntitySubscriber $goneEntitySubscriber;

    /**
     * @var ObjectProphecy<RouteRepositoryInterface>
     */
    private ObjectProphecy $routeRepository;

    protected function setUp(): void
    {
        $this->routeRepository = $this->prophesize(RouteRepositoryInterface::class);

        $this->goneEntitySubscriber = new GoneEntitySubscriber(
            $this->routeRepository->reveal()
        );
    }

    public function testPreRemoveWithNonContentRichEntity(): void
    {
        $event = $this->prophesize(LifecycleEventArgs::class);
        $event->getObject()->willReturn(new \stdClass());

        $this->routeRepository->findBy(Argument::any())->shouldNotBeCalled();

        $this->goneEntitySubscriber->preRemove($event->reveal());
    }

    public function testPreRemoveWithContentRichEntity(): void
    {
        $page = new Page();

        $event = $this->prophesize(LifecycleEventArgs::class);
        $event->getObject()->willReturn($page);

        $this->goneEntitySubscriber->preRemove($event->reveal());

        $this->assertTrue(true);
    }

    public function testPostFlushCreatesRedirects(): void
    {
        $page = new Page();

        $preRemoveEvent = $this->prophesize(LifecycleEventArgs::class);
        $preRemoveEvent->getObject()->willReturn($page);

        $route1 = $this->prophesize(Route::class);
        $route1->getSlug()->willReturn('/test-page');
        $route1->getLocale()->willReturn('en');
        $route1->isHistory()->willReturn(false);

        $route2 = $this->prophesize(Route::class);
        $route2->getSlug()->willReturn('/old-test-page');
        $route2->getLocale()->willReturn('en');
        $route2->isHistory()->willReturn(true);

        $this->routeRepository->findBy([
            'resourceKey' => 'pages',
            'resourceId' => $page->getId(),
        ])->willReturn([$route1->reveal(), $route2->reveal()]);

        $connection = $this->prophesize(Connection::class);
        $connection->fetchOne(
            'SELECT id FROM re_redirect_routes WHERE source = :source AND sourceHost IS NULL',
            ['source' => '/en/test-page']
        )->willReturn(false);
        $connection->insert('re_redirect_routes', Argument::that(function(array $data) {
            return '/en/test-page' === $data['source']
                && 410 === $data['statusCode']
                && true === $data['enabled']
                && '' === $data['target']
                && null === $data['sourceHost'];
        }))->shouldBeCalledTimes(1);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()->willReturn($connection->reveal());

        $this->goneEntitySubscriber->preRemove($preRemoveEvent->reveal());

        $postFlushEvent = $this->prophesize(PostFlushEventArgs::class);
        $postFlushEvent->getObjectManager()->willReturn($entityManager->reveal());
        $this->goneEntitySubscriber->postFlush($postFlushEvent->reveal());
    }

    public function testPostFlushSkipsDuplicates(): void
    {
        $page = new Page();

        $preRemoveEvent = $this->prophesize(LifecycleEventArgs::class);
        $preRemoveEvent->getObject()->willReturn($page);

        $route = $this->prophesize(Route::class);
        $route->getSlug()->willReturn('/existing-page');
        $route->getLocale()->willReturn('en');
        $route->isHistory()->willReturn(false);

        $this->routeRepository->findBy([
            'resourceKey' => 'pages',
            'resourceId' => $page->getId(),
        ])->willReturn([$route->reveal()]);

        $connection = $this->prophesize(Connection::class);
        $connection->fetchOne(
            'SELECT id FROM re_redirect_routes WHERE source = :source AND sourceHost IS NULL',
            ['source' => '/en/existing-page']
        )->willReturn('some-existing-id');
        $connection->insert(Argument::cetera())->shouldNotBeCalled();

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()->willReturn($connection->reveal());

        $this->goneEntitySubscriber->preRemove($preRemoveEvent->reveal());

        $postFlushEvent = $this->prophesize(PostFlushEventArgs::class);
        $postFlushEvent->getObjectManager()->willReturn($entityManager->reveal());
        $this->goneEntitySubscriber->postFlush($postFlushEvent->reveal());
    }

    public function testPostFlushWithNoRemovedEntities(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()->shouldNotBeCalled();

        $event = $this->prophesize(PostFlushEventArgs::class);
        $event->getObjectManager()->willReturn($entityManager->reveal());

        $this->routeRepository->findBy(Argument::any())->shouldNotBeCalled();

        $this->goneEntitySubscriber->postFlush($event->reveal());
    }

    public function testReset(): void
    {
        $page = new Page();

        $event = $this->prophesize(LifecycleEventArgs::class);
        $event->getObject()->willReturn($page);

        $this->goneEntitySubscriber->preRemove($event->reveal());
        $this->goneEntitySubscriber->reset();

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()->shouldNotBeCalled();

        $postFlushEvent = $this->prophesize(PostFlushEventArgs::class);
        $postFlushEvent->getObjectManager()->willReturn($entityManager->reveal());
        $this->routeRepository->findBy(Argument::any())->shouldNotBeCalled();

        $this->goneEntitySubscriber->postFlush($postFlushEvent->reveal());
    }
}
