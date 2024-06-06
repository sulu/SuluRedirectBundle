<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\GoneSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\DocumentManagerBundle\Bridge\DocumentInspector;
use Sulu\Bundle\PageBundle\Document\BasePageDocument;
use Sulu\Bundle\RedirectBundle\Exception\RedirectRouteNotUniqueException;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Sulu\Component\Content\Types\ResourceLocator\Strategy\ResourceLocatorStrategyInterface;
use Sulu\Component\Content\Types\ResourceLocator\Strategy\ResourceLocatorStrategyPoolInterface;
use Sulu\Component\DocumentManager\Event\RemoveEvent;
use Sulu\Component\DocumentManager\Events;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This gone subscriber listens for removed pages.
 *
 * @internal this is a internal listener which should not be used directly
 */
class GoneDocumentSubscriber implements EventSubscriberInterface
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
    protected $environment;

    /**
     * @param string $environment
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RedirectRouteManager $redirectRouteManager,
        DocumentInspector $documentInspector,
        WebspaceManagerInterface $webspaceManager,
        ResourceLocatorStrategyPoolInterface $resourceLocatorStrategyPool,
        $environment
    ) {
        $this->entityManager = $entityManager;
        $this->redirectRouteManager = $redirectRouteManager;
        $this->documentInspector = $documentInspector;
        $this->webspaceManager = $webspaceManager;
        $this->resourceLocatorStrategyPool = $resourceLocatorStrategyPool;
        $this->environment = $environment;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REMOVE => ['createRedirects', 2048],
        ];
    }

    public function createRedirects(RemoveEvent $event): void
    {
        $document = $event->getDocument();

        if (!$document instanceof BasePageDocument) {
            return;
        }

        foreach ($this->getUrls($document) as $url) {
            try {
                $this->redirectRouteManager->saveByData([
                    'source' => $url,
                    'sourceHost' => null,
                    'statusCode' => 410,
                    'enabled' => true,
                    'target' => '',
                ]);
            } catch (RedirectRouteNotUniqueException $exception) {
                // do nothing when there already exists a redirect route
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @return string[]
     */
    protected function getUrls(BasePageDocument $document)
    {
        $urls = [];
        $webspaceKey = $this->documentInspector->getWebspace($document);
        $resourceLocatorStrategy = $this->resourceLocatorStrategyPool->getStrategyByWebspaceKey($webspaceKey);

        $webspace = $this->webspaceManager->findWebspaceByKey($webspaceKey);

        if (!$webspace) {
            return $urls;
        }

        $localizedUrls = $this->documentInspector->getLocalizedUrlsForPage($document);

        foreach ($webspace->getAllLocalizations() as $localization) {
            if (!array_key_exists($localization->getLocale(), $localizedUrls)) {
                continue;
            }

            $urls = array_merge(
                $this->webspaceManager->findUrlsByResourceLocator(
                    $localizedUrls[$localization->getLocale()],
                    $this->environment,
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

        return array_unique($urls);
    }

    /**
     * @param string $uuid
     * @param string $webspaceKey
     * @param string $locale
     *
     * @return string[]
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
                    $this->environment,
                    $locale
                ),
                $historyUrls
            );
        }

        return $historyUrls;
    }
}
