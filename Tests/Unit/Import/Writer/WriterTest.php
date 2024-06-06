<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Import\Writer;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\RedirectBundle\Exception\RedirectRouteNotUniqueException;
use Sulu\Bundle\RedirectBundle\Import\Writer\DuplicatedSourceException;
use Sulu\Bundle\RedirectBundle\Import\Writer\TargetIsEmptyException;
use Sulu\Bundle\RedirectBundle\Import\Writer\Writer;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManagerInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Symfony\Component\HttpFoundation\Response;

class WriterTest extends TestCase
{
    /**
     * @var ObjectProphecy<RedirectRouteManagerInterface>
     */
    private $redirectRouteManager;

    /**
     * @var ObjectProphecy<EntityManagerInterface>
     */
    private $entityManager;

    /**
     * @var Writer
     */
    private $writer;

    protected function setUp(): void
    {
        $this->redirectRouteManager = $this->prophesize(RedirectRouteManagerInterface::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);

        $this->writer = new Writer($this->redirectRouteManager->reveal(), $this->entityManager->reveal());
    }

    public function testWrite()
    {
        $entities = [
            $this->prophesize(RedirectRouteInterface::class),
            $this->prophesize(RedirectRouteInterface::class),
            $this->prophesize(RedirectRouteInterface::class),
        ];

        for ($i = 0; $i < count($entities); ++$i) {
            $entities[$i]->getSource()->willReturn('/source-' . $i);
            $entities[$i]->getTarget()->willReturn('/target-' . $i);
        }

        $this->writer->write($entities[0]->reveal());
        $this->writer->write($entities[1]->reveal());
        $this->writer->write($entities[2]->reveal());

        $this->redirectRouteManager->save($entities[0]->reveal())->shouldBeCalled();
        $this->redirectRouteManager->save($entities[1]->reveal())->shouldBeCalled();
        $this->redirectRouteManager->save($entities[2]->reveal())->shouldBeCalled();

        $this->entityManager->flush()->shouldNotBeCalled();
    }

    public function testWriteSmallBatchSize()
    {
        $this->writer->setBatchSize(2);

        $entities = [
            $this->prophesize(RedirectRouteInterface::class),
            $this->prophesize(RedirectRouteInterface::class),
            $this->prophesize(RedirectRouteInterface::class),
        ];

        for ($i = 0; $i < count($entities); ++$i) {
            $entities[$i]->getSource()->willReturn('/source-' . $i);
            $entities[$i]->getTarget()->willReturn('/target-' . $i);
        }

        $this->writer->write($entities[0]->reveal());
        $this->writer->write($entities[1]->reveal());
        $this->writer->write($entities[2]->reveal());

        $this->redirectRouteManager->save($entities[0]->reveal())->shouldBeCalled();
        $this->redirectRouteManager->save($entities[1]->reveal())->shouldBeCalled();
        $this->redirectRouteManager->save($entities[2]->reveal())->shouldBeCalled();

        $this->entityManager->flush()->shouldBeCalledTimes(1);
    }

    public function testWriteDuplicated()
    {
        $this->expectException(DuplicatedSourceException::class);

        $entities = [
            $this->prophesize(RedirectRouteInterface::class),
            $this->prophesize(RedirectRouteInterface::class),
        ];

        for ($i = 0; $i < count($entities); ++$i) {
            $entities[$i]->getSource()->willReturn('/source');
            $entities[$i]->getTarget()->willReturn('/target');
        }

        $this->writer->write($entities[0]->reveal());
        $this->writer->write($entities[1]->reveal());

        $this->redirectRouteManager->save($entities[0]->reveal())->shouldBeCalled();
        $this->redirectRouteManager->save($entities[1]->reveal())->shouldNotBeCalled();
    }

    public function testWriteDuplicatedCaseInSensitive()
    {
        $this->expectException(DuplicatedSourceException::class);

        $entities = [
            $this->prophesize(RedirectRouteInterface::class),
            $this->prophesize(RedirectRouteInterface::class),
        ];

        $entities[0]->getSource()->willReturn('/source');
        $entities[0]->getTarget()->willReturn('/target');

        $entities[1]->getSource()->willReturn('/Source');
        $entities[1]->getTarget()->willReturn('/target');

        $this->writer->write($entities[0]->reveal());
        $this->writer->write($entities[1]->reveal());

        $this->redirectRouteManager->save($entities[0]->reveal())->shouldBeCalled();
        $this->redirectRouteManager->save($entities[1]->reveal())->shouldNotBeCalled();
    }

    public function testWriteAlreadyExisting()
    {
        $this->expectException(DuplicatedSourceException::class);

        $entity = $this->prophesize(RedirectRouteInterface::class);
        $entity->getSource()->willReturn('/source');
        $entity->getTarget()->willReturn('/target');

        $this->redirectRouteManager->save($entity->reveal())
            ->shouldBeCalled()
            ->willThrow($this->prophesize(RedirectRouteNotUniqueException::class)->reveal());

        $this->writer->write($entity->reveal());
    }

    public function testWriteEmptyTarget()
    {
        $this->expectException(TargetIsEmptyException::class);

        $entity = $this->prophesize(RedirectRouteInterface::class);
        $entity->getSource()->willReturn('/source');
        $entity->getTarget()->willReturn('');
        $entity->getStatusCode()->willReturn(Response::HTTP_MOVED_PERMANENTLY);

        $this->redirectRouteManager->save($entity->reveal())->shouldNotBeCalled();

        $this->writer->write($entity->reveal());
    }

    public function testWriteEmptyTargetFor410()
    {
        $entity = $this->prophesize(RedirectRouteInterface::class);
        $entity->getSource()->willReturn('/source');
        $entity->getTarget()->willReturn('');
        $entity->getStatusCode()->willReturn(Response::HTTP_GONE);

        $this->redirectRouteManager->save($entity->reveal())->shouldBeCalled();

        $this->writer->write($entity->reveal());

        $this->redirectRouteManager->save($entity->reveal())->shouldBeCalled();
    }

    public function testFinalize()
    {
        $this->writer->finalize();
        $this->entityManager->flush()->shouldBeCalled();
    }
}
