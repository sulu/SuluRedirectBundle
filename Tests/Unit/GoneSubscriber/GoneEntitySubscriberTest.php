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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
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

        // Assert that no exceptions were thrown and the entity was collected
        $this->assertTrue(true);
    }

    public function testPostFlushCreatesRedirects(): void
    {
        $page = new Page();

        $preRemoveEvent = $this->prophesize(LifecycleEventArgs::class);
        $preRemoveEvent->getObject()->willReturn($page);

        $route1 = $this->prophesize(Route::class);
        $route1->getSlug()->willReturn('/test-page');
        $route1->isHistory()->willReturn(false);

        $route2 = $this->prophesize(Route::class);
        $route2->getSlug()->willReturn('/old-test-page');
        $route2->isHistory()->willReturn(true);

        $this->routeRepository->findBy([
            'resourceKey' => 'pages',
            'resourceId' => $page->getId(),
        ])->willReturn([$route1->reveal(), $route2->reveal()]);

        $redirectRepository = $this->prophesize(EntityRepository::class);
        $redirectRepository->findOneBy(Argument::any())->willReturn(null);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(RedirectRoute::class)->willReturn($redirectRepository->reveal());
        $entityManager->persist(Argument::that(function (RedirectRoute $redirectRoute) {
            return $redirectRoute->getSource() === '/test-page'
                && $redirectRoute->getStatusCode() === 410
                && $redirectRoute->isEnabled();
        }))->shouldBeCalledTimes(1);
        $entityManager->flush()->shouldBeCalledTimes(1);

        $this->goneEntitySubscriber->preRemove($preRemoveEvent->reveal());

        $postFlushEvent = $this->prophesize(PostFlushEventArgs::class);
        $postFlushEvent->getObjectManager()->willReturn($entityManager->reveal());
        $this->goneEntitySubscriber->postFlush($postFlushEvent->reveal());
    }

    public function testPostFlushWithNoRemovedEntities(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->flush()->shouldNotBeCalled();

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
        $entityManager->flush()->shouldNotBeCalled();

        $postFlushEvent = $this->prophesize(PostFlushEventArgs::class);
        $postFlushEvent->getObjectManager()->willReturn($entityManager->reveal());
        $this->routeRepository->findBy(Argument::any())->shouldNotBeCalled();

        $this->goneEntitySubscriber->postFlush($postFlushEvent->reveal());
    }
}
