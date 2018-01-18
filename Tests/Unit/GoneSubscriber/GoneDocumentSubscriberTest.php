<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\GoneSubscriber;

use Doctrine\ORM\EntityManager;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Document\BasePageDocument;
use Sulu\Bundle\DocumentManagerBundle\Bridge\DocumentInspector;
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

class GoneDocumentSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GoneDocumentSubscriber
     */
    private $goneDocumentSubscriber;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var RedirectRouteManager
     */
    private $redirectRouteManager;

    /**
     * @var DocumentInspector
     */
    private $documentInspector;

    /**
     * @var WebspaceManager
     */
    private $webspaceManager;

    /**
     * @var ResourceLocatorStrategyPool
     */
    private $resourceLocatorStrategyPool;

    /**
     * @var RemoveEvent
     */
    private $removeEvent;

    /**
     * @var BasePageDocument
     */
    private $document;

    /**
     * @var ResourceLocatorStrategyInterface
     */
    private $resourceLocatorStrategy;

    /**
     * @var Webspace
     */
    private $webspace;

    protected function setUp()
    {
        $this->document = $this->prophesize(BasePageDocument::class);
        $this->document->getUuid()->willReturn('123-123-123');
        $this->document->getResourceSegment()->willReturn('/abc');

        $this->entityManager = $this->prophesize(EntityManager::class);

        $this->redirectRouteManager = $this->prophesize(RedirectRouteManager::class);

        $this->documentInspector = $this->prophesize(DocumentInspector::class);
        $this->documentInspector->getWebspace($this->document)->willReturn('example');

        $this->webspace = $this->prophesize(Webspace::class);
        $this->webspace->getAllLocalizations()->willReturn([
            new Localization('en'),
            new Localization('de'),
        ]);

        $this->webspaceManager = $this->prophesize(WebspaceManager::class);
        $this->webspaceManager->findWebspaceByKey('example')->willReturn($this->webspace->reveal());
        $this->webspaceManager->findUrlsByResourceLocator('/abc', 'test', 'en')
            ->willReturn(['http://{host}/en/abc']);
        $this->webspaceManager->findUrlsByResourceLocator('/abc1', 'test', 'en')
            ->willReturn(['http://{host}/en/abc1']);
        $this->webspaceManager->findUrlsByResourceLocator('/abc2', 'test', 'en')
            ->willReturn(['http://{host}/en/abc2']);
        $this->webspaceManager->findUrlsByResourceLocator('/abc', 'test', 'de')
            ->willReturn(['http://{host}/en/abc']);
        $this->webspaceManager->findUrlsByResourceLocator('/abc1', 'test', 'de')
            ->willReturn(['http://{host}/en/abc1']);
        $this->webspaceManager->findUrlsByResourceLocator('/abc2', 'test', 'de')
            ->willReturn(['http://{host}/en/abc2']);

        $this->resourceLocatorStrategy = $this->prophesize(ResourceLocatorStrategyInterface::class);
        $this->resourceLocatorStrategy->loadHistoryByContentUuid('123-123-123', 'example', 'en')
            ->willReturn(
                [
                    new ResourceLocatorInformation('/abc1', new \DateTime(), '1'),
                    new ResourceLocatorInformation('/abc2', new \DateTime(), '1'),
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

        $this->redirectRouteManager->save()->shouldNotBeCalled();
        $this->entityManager->flush()->shouldNotBeCalled();

        $this->goneDocumentSubscriber->createRedirects($this->removeEvent->reveal());
    }

    public function testCreateRedirects()
    {
        $this->redirectRouteManager->save(Argument::any())->shouldBeCalledTimes(4);
        $this->entityManager->flush()->shouldBeCalled();

        $this->goneDocumentSubscriber->createRedirects($this->removeEvent->reveal());
    }
}
