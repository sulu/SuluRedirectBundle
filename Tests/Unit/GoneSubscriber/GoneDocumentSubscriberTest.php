<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\GoneSubscriber;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\DocumentManagerBundle\Bridge\DocumentInspector;
use Sulu\Bundle\PageBundle\Document\BasePageDocument;
use Sulu\Bundle\RedirectBundle\GoneSubscriber\GoneDocumentSubscriber;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Sulu\Bundle\SnippetBundle\Document\SnippetDocument;
use Sulu\Component\Content\Types\ResourceLocator\ResourceLocatorInformation;
use Sulu\Component\Content\Types\ResourceLocator\Strategy\ResourceLocatorStrategyInterface;
use Sulu\Component\Content\Types\ResourceLocator\Strategy\ResourceLocatorStrategyPool;
use Sulu\Component\DocumentManager\Event\RemoveEvent;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceManager;
use Sulu\Component\Webspace\Webspace;

class GoneDocumentSubscriberTest extends TestCase
{
    /**
     * @var GoneDocumentSubscriber
     */
    private $goneDocumentSubscriber;

    /**
     * @var ObjectProphecy<EntityManager>
     */
    private $entityManager;

    /**
     * @var ObjectProphecy<RedirectRouteManager>
     */
    private $redirectRouteManager;

    /**
     * @var ObjectProphecy<DocumentInspector>
     */
    private $documentInspector;

    /**
     * @var ObjectProphecy<WebspaceManager>
     */
    private $webspaceManager;

    /**
     * @var ObjectProphecy<ResourceLocatorStrategyPool>
     */
    private $resourceLocatorStrategyPool;

    /**
     * @var ObjectProphecy<RemoveEvent>
     */
    private $removeEvent;

    /**
     * @var ObjectProphecy<BasePageDocument>
     */
    private $document;

    /**
     * @var ObjectProphecy<ResourceLocatorStrategyInterface>
     */
    private $resourceLocatorStrategy;

    /**
     * @var ObjectProphecy<Webspace>
     */
    private $webspace;

    protected function setUp(): void
    {
        $this->document = $this->prophesize(BasePageDocument::class);
        $this->document->getUuid()->willReturn('123-123-123');

        $this->entityManager = $this->prophesize(EntityManager::class);

        $this->redirectRouteManager = $this->prophesize(RedirectRouteManager::class);

        $this->documentInspector = $this->prophesize(DocumentInspector::class);
        $this->documentInspector->getWebspace($this->document->reveal())->willReturn('example');

        $this->documentInspector->getLocalizedUrlsForPage($this->document->reveal())->willReturn(['de' => '/artikel', 'en' => '/article']);

        $this->webspace = $this->prophesize(Webspace::class);
        $this->webspace->getAllLocalizations()->willReturn([
            new Localization('en'),
            new Localization('de'),
        ]);

        $this->webspaceManager = $this->prophesize(WebspaceManager::class);
        $this->webspaceManager->findWebspaceByKey('example')->willReturn($this->webspace->reveal());
        $this->webspaceManager->findUrlsByResourceLocator('/article', 'test', 'en')
            ->willReturn(['http://{host}/en/article', 'http://sulu.io/en/article']);
        $this->webspaceManager->findUrlsByResourceLocator('/article1', 'test', 'en')
            ->willReturn(['http://{host}/en/article1']);
        $this->webspaceManager->findUrlsByResourceLocator('/article2', 'test', 'en')
            ->willReturn(['http://{host}/en/article2']);
        $this->webspaceManager->findUrlsByResourceLocator('/artikel', 'test', 'de')
            ->willReturn(['http://{host}/de/artikel']);

        $this->resourceLocatorStrategy = $this->prophesize(ResourceLocatorStrategyInterface::class);
        $this->resourceLocatorStrategy->loadHistoryByContentUuid('123-123-123', 'example', 'en')
            ->willReturn(
                [
                    new ResourceLocatorInformation('/article1', new \DateTime(), '1'),
                    new ResourceLocatorInformation('/article2', new \DateTime(), '1'),
                ]
            );
        $this->resourceLocatorStrategy->loadHistoryByContentUuid('123-123-123', 'example', 'de')
            ->willReturn([]);

        $this->resourceLocatorStrategyPool = $this->prophesize(ResourceLocatorStrategyPool::class);
        $this->resourceLocatorStrategyPool->getStrategyByWebspaceKey('example')
            ->willReturn($this->resourceLocatorStrategy->reveal());

        $this->removeEvent = $this->prophesize(RemoveEvent::class);
        $this->removeEvent->getDocument()->willReturn($this->document->reveal());

        $this->goneDocumentSubscriber = new GoneDocumentSubscriber(
            $this->entityManager->reveal(),
            $this->redirectRouteManager->reveal(),
            $this->documentInspector->reveal(),
            $this->webspaceManager->reveal(),
            $this->resourceLocatorStrategyPool->reveal(),
            'test'
        );
    }

    public function testCreateRedirectsWithWrongDocument()
    {
        $wrongDocument = $this->prophesize(SnippetDocument::class);
        $this->removeEvent->getDocument()->willReturn($wrongDocument->reveal());

        $this->redirectRouteManager->saveByData()->shouldNotBeCalled();
        $this->entityManager->flush()->shouldNotBeCalled();

        $this->goneDocumentSubscriber->createRedirects($this->removeEvent->reveal());
    }

    public function testCreateRedirects()
    {
        $this->redirectRouteManager->saveByData(Argument::any())->shouldBeCalledTimes(4);
        $this->entityManager->flush()->shouldBeCalled();

        $this->goneDocumentSubscriber->createRedirects($this->removeEvent->reveal());
    }
}
