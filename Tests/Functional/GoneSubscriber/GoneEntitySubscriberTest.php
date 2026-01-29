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

namespace Sulu\Bundle\RedirectBundle\Tests\Functional\GoneSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Page\Domain\Model\Page;
use Sulu\Page\Domain\Model\PageDimensionContent;
use Sulu\Route\Domain\Model\Route;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;

class GoneEntitySubscriberTest extends SuluTestCase
{
    private EntityManagerInterface $entityManager;
    private RouteRepositoryInterface $routeRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        self::purgeDatabase();

        $this->entityManager = self::getEntityManager();
        $this->routeRepository = self::getContainer()->get('sulu_route.route_repository');
    }

    public function testDeletePageCreates410Redirects(): void
    {
        // Create a page with routes
        $page = new Page();
        $page->setWebspaceKey('sulu_io');
        $this->entityManager->persist($page);

        $dimensionContent = new PageDimensionContent($page);
        $dimensionContent->setLocale('en');
        $dimensionContent->setStage('live');
        $dimensionContent->setTemplateKey('overview');
        $this->entityManager->persist($dimensionContent);

        $route = new Route('pages', $page->getId(), 'en', '/test-page', 'sulu_io');
        $this->entityManager->persist($route);

        $this->entityManager->flush();
        $pageId = $page->getId();
        $this->entityManager->clear();

        // Verify route exists
        $routes = $this->routeRepository->findBy([
            'resourceKey' => 'pages',
            'resourceId' => $pageId,
        ]);
        $this->assertCount(1, $routes);
        $route = $routes[0];
        $this->assertInstanceOf(Route::class, $route);
        $routeSlug = $route->getSlug();

        // Delete the page
        $page = $this->entityManager->find(Page::class, $pageId);
        $this->entityManager->remove($page);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Verify routes are deleted
        $routes = $this->routeRepository->findBy([
            'resourceKey' => 'pages',
            'resourceId' => $pageId,
        ]);
        $this->assertCount(0, $routes);

        // Verify 410 redirect was created
        $redirectRepository = $this->entityManager->getRepository(RedirectRoute::class);
        $expectedSource = '/en' . $routeSlug;
        $redirects = $redirectRepository->findBy(['source' => $expectedSource]);
        $this->assertCount(1, $redirects);
        $this->assertSame(410, $redirects[0]->getStatusCode());
        $this->assertTrue($redirects[0]->isEnabled());
        $this->assertSame('', $redirects[0]->getTarget());
    }

    public function testDeletePageSkipsHistoryRoutes(): void
    {
        // Create a page with regular route and history route
        $page = new Page();
        $page->setWebspaceKey('sulu_io');
        $this->entityManager->persist($page);

        $dimensionContent = new PageDimensionContent($page);
        $dimensionContent->setLocale('en');
        $dimensionContent->setStage('live');
        $dimensionContent->setTemplateKey('overview');
        $this->entityManager->persist($dimensionContent);

        $route = new Route('pages', $page->getId(), 'en', '/current-page', 'sulu_io');
        $this->entityManager->persist($route);

        // History routes use Route::HISTORY_RESOURCE_KEY and format: 'pages::uuid'
        $historyRoute = new Route(Route::HISTORY_RESOURCE_KEY, 'pages::' . $page->getId(), 'en', '/old-page', 'sulu_io');
        $this->entityManager->persist($historyRoute);

        $this->entityManager->flush();
        $pageId = $page->getId();
        $this->entityManager->clear();

        // Delete the page
        $page = $this->entityManager->find(Page::class, $pageId);
        $this->entityManager->remove($page);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Verify only 1 redirect was created (for non-history route)
        $redirectRepository = $this->entityManager->getRepository(RedirectRoute::class);
        $allRedirects = $redirectRepository->findAll();
        $this->assertCount(1, $allRedirects);
        $this->assertSame('/en/current-page', $allRedirects[0]->getSource());
    }
}
