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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\OnClearEventArgs;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\RoutableInterface;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
use Symfony\Component\Uid\Uuid;
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
        if (0 === \count($this->removedContentRichEntityIds)) {
            return;
        }

        try {
            $connection = $args->getObjectManager()->getConnection();
            $groupedByResourceKey = [];
            foreach ($this->removedContentRichEntityIds as $entityInfo) {
                $groupedByResourceKey[$entityInfo['resourceKey']][] = $entityInfo['resourceId'];
            }

            foreach ($groupedByResourceKey as $resourceKey => $resourceIds) {
                $this->createRedirectsForRoutes($connection, $resourceKey, $resourceIds);
            }
        } finally {
            $this->reset();
        }
    }

    public function reset(): void
    {
        $this->removedContentRichEntityIds = [];
    }

    /**
     * @param string[] $resourceIds
     */
    private function createRedirectsForRoutes(Connection $connection, string $resourceKey, array $resourceIds): void
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

                $source = \mb_strtolower('/' . \ltrim($route->getSlug(), '/'));

                $existing = $connection->fetchOne(
                    'SELECT id FROM re_redirect_routes WHERE source = :source AND sourceHost IS NULL',
                    ['source' => $source]
                );

                if (false !== $existing) {
                    continue;
                }

                $now = new \DateTimeImmutable();

                $connection->insert('re_redirect_routes', [
                    'id' => Uuid::v7()->toRfc4122(),
                    'enabled' => true,
                    'statusCode' => 410,
                    'source' => $source,
                    'sourceHost' => null,
                    'target' => '',
                    'created' => $now->format('Y-m-d H:i:s'),
                    'changed' => $now->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
