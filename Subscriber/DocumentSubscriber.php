<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Sulu\Bundle\ContentBundle\Document\BasePageDocument;
use Sulu\Bundle\DocumentManagerBundle\Bridge\DocumentInspector;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteNotUniqueException;
use Sulu\Component\Content\Types\ResourceLocator\Strategy\ResourceLocatorStrategyInterface;
use Sulu\Component\Content\Types\ResourceLocator\Strategy\ResourceLocatorStrategyPoolInterface;
use Sulu\Component\DocumentManager\Event\RemoveEvent;
use Sulu\Component\DocumentManager\Events;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DocumentSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var RedirectRouteManager
     */
    protected $redirectRouteManager;

    /**
     * @var DocumentInspector
     */
    protected $documentInspector;

    /**
     * @var WebspaceManagerInterface
     */
    protected $webspaceManager;

    /**
     * @var ResourceLocatorStrategyPoolInterface
     */
    protected $resourceLocatorStrategyPool;

    /**
     * @var string
     */
    protected $env;

    public function __construct(
        EntityManagerInterface $entityManager,
        RedirectRouteManager $redirectRouteManager,
        DocumentInspector $documentInspector,
        WebspaceManagerInterface $webspaceManager,
        ResourceLocatorStrategyPoolInterface $resourceLocatorStrategyPool,
        $env
    ) {
        $this->entityManager = $entityManager;
        $this->redirectRouteManager = $redirectRouteManager;
        $this->documentInspector = $documentInspector;
        $this->webspaceManager = $webspaceManager;
        $this->resourceLocatorStrategyPool = $resourceLocatorStrategyPool;
        $this->env = $env;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::REMOVE => ['createRedirects', 2048],

        ];
    }

    /**
     * @param RemoveEvent $event
     */
    public function createRedirects(RemoveEvent $event)
    {
        $document = $event->getDocument();

        if (!$document instanceof BasePageDocument) {
            return;
        }

        foreach ($this->getUrls($document) as $url) {
            $redirectRoute = new RedirectRoute();
            $redirectRoute->setId(Uuid::uuid4()->toString());
            $redirectRoute->setEnabled(true);
            $redirectRoute->setStatusCode(410);
            $redirectRoute->setSource($url);

            try {
                $this->redirectRouteManager->save($redirectRoute);
            } catch (RedirectRouteNotUniqueException $exception) {
                // do nothing when there already exists a redirect route
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param BasePageDocument $document
     *
     * @return array
     */
    protected function getUrls(BasePageDocument $document)
    {
        $urls = [];
        $webspaceKey = $this->documentInspector->getWebspace($document);
        $resourceLocatorStrategy = $this->resourceLocatorStrategyPool->getStrategyByWebspaceKey($webspaceKey);

        foreach ($this->webspaceManager->findWebspaceByKey($webspaceKey)->getAllLocalizations() as $localization) {
            $urls = array_merge(
                $this->webspaceManager->findUrlsByResourceLocator(
                    $document->getResourceSegment(),
                    $this->env,
                    $localization->getLocale()
                ),
                $urls
            );

            $urls = array_merge(
                $this->getHistoryUrls(
                    $resourceLocatorStrategy,
                    $document->getUuid(),
                    $webspaceKey,
                    $localization->getLocale()
                ),
                $urls
            );
        }

        foreach ($urls as &$url) {
            $url = parse_url($url, PHP_URL_PATH);
        }

        return $urls;
    }

    /**
     * @param ResourceLocatorStrategyInterface $resourceLocatorStrategy
     * @param string $uuid
     * @param string $webspaceKey
     * @param string $locale
     *
     * @return array
     */
    protected function getHistoryUrls(
        ResourceLocatorStrategyInterface $resourceLocatorStrategy,
        $uuid,
        $webspaceKey,
        $locale
    ) {
        $historyUrls = [];
        foreach ($resourceLocatorStrategy->loadHistoryByContentUuid($uuid, $webspaceKey, $locale) as $history) {
            $historyUrls = array_merge(
                $this->webspaceManager->findUrlsByResourceLocator(
                    $history->getResourceLocator(),
                    $this->env,
                    $locale
                ),
                $historyUrls
            );
        }

        return $historyUrls;
    }
}