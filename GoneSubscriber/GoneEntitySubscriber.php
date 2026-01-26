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

namespace Sulu\Bundle\RedirectBundle\GoneSubscriber;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\OnClearEventArgs;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\RoutableInterface;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * This gone subscriber listens for removed routable entities and creates 410 redirects.
 *
 * @internal this is a internal listener which should not be used directly
 */
class GoneEntitySubscriber implements ResetInterface
{
    /**
     * @var array<array{resourceKey: string, resourceId: string}>
     */
    private array $removedContentRichEntityIds = [];

    /**
     * @var array<class-string, string|null>
     */
    private array $resourceFieldNameCache = [];

    /**
     * Flag to prevent infinite recursion when calling flush() in postFlush.
     */
    private bool $isProcessing = false;

    public function __construct(
        private RouteRepositoryInterface $routeRepository
    ) {
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof ContentRichEntityInterface) {
            return;
        }

        $dimensionContentClass = $object->createDimensionContent()::class;
        if (!\is_subclass_of($dimensionContentClass, RoutableInterface::class)) {
            return;
        }

        $this->removedContentRichEntityIds[] = [
            'resourceKey' => $dimensionContentClass::getResourceKey(),
            'resourceId' => (string) $object->getId(),
        ];
    }

    public function onClear(OnClearEventArgs $args): void
    {
        $this->reset();
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->isProcessing || 0 === \count($this->removedContentRichEntityIds)) {
            return;
        }

        $this->isProcessing = true;

        try {
            $objectManager = $args->getObjectManager();
            $groupedByResourceKey = [];
            foreach ($this->removedContentRichEntityIds as $entityInfo) {
                $groupedByResourceKey[$entityInfo['resourceKey']][] = $entityInfo['resourceId'];
            }

            foreach ($groupedByResourceKey as $resourceKey => $resourceIds) {
                $this->createRedirectsForRoutes($objectManager, $resourceKey, $resourceIds);
            }

            $objectManager->flush();
        } finally {
            $this->isProcessing = false;
            $this->reset();
        }
    }

    public function reset(): void
    {
        $this->removedContentRichEntityIds = [];
        $this->resourceFieldNameCache = [];
        $this->isProcessing = false;
    }

    /**
     * @param string[] $resourceIds
     */
    private function createRedirectsForRoutes(\Doctrine\ORM\EntityManagerInterface $objectManager, string $resourceKey, array $resourceIds): void
    {
        if (0 === \count($resourceIds)) {
            return;
        }

        foreach ($resourceIds as $resourceId) {
            $routes = $this->routeRepository->findBy([
                'resourceKey' => $resourceKey,
                'resourceId' => $resourceId,
            ]);

            foreach ($routes as $route) {
                if ($route->isHistory()) {
                    continue;
                }

                $redirectRoute = new RedirectRoute();
                $redirectRoute->setId(\Ramsey\Uuid\Uuid::uuid4()->toString());
                $redirectRoute->setEnabled(true);
                $redirectRoute->setStatusCode(410);
                $redirectRoute->setSource($route->getSlug());
                $redirectRoute->setTarget('');

                // Check for duplicate before persisting
                $existing = $objectManager->getRepository(RedirectRoute::class)->findOneBy([
                    'source' => $redirectRoute->getSource(),
                    'sourceHost' => $redirectRoute->getSourceHost(),
                ]);

                if (!$existing) {
                    $objectManager->persist($redirectRoute);
                }
            }
        }
    }
}
