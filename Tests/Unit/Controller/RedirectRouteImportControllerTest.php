<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\RedirectBundle\Controller\RedirectRouteImportController;
use Sulu\Bundle\RedirectBundle\Import\Converter\ConverterNotFoundException;
use Sulu\Bundle\RedirectBundle\Import\FileImportInterface;
use Sulu\Bundle\RedirectBundle\Import\ImportException;
use Sulu\Bundle\RedirectBundle\Import\Item;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderNotFoundException;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RedirectRouteImportControllerTest extends TestCase
{
    /**
     * @var string
     */
    private $importPath = __DIR__ . '/../../Application/var/uploads/imports';

    /**
     * @var string
     */
    private $fileName = 'test.csv';

    public function testImportAction()
    {
        $request = $this->prophesize(Request::class);

        $fileBag = $this->prophesize(FileBag::class);
        $request->reveal()->files = $fileBag->reveal();

        $uploadedFile = $this->createUploadedFile(__DIR__ . '/import.csv');

        $fileBag->has('redirectRoutes')->willReturn(true);
        $fileBag->get('redirectRoutes')->willReturn($uploadedFile);

        $items = [
            new Item(1, '', $this->prophesize(RedirectRouteInterface::class)->reveal()),
            new Item(2, '', null, $this->prophesize(ImportException::class)->reveal()),
            new Item(3, '', $this->prophesize(RedirectRouteInterface::class)->reveal()),
        ];

        $import = $this->prophesize(FileImportInterface::class);
        $import->import(Argument::any())->willReturn($items);

        $controller = new RedirectRouteImportController($import->reveal(), $this->importPath);
        $response = $controller->postAction($request->reveal());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(3, $data['total']);
        $this->assertCount(1, $data['exceptions']);

        $this->assertEquals(2, $data['exceptions'][0]['lineNumber']);
    }

    public function testImportActionReaderNotFound()
    {
        $request = $this->prophesize(Request::class);

        $fileBag = $this->prophesize(FileBag::class);
        $request->reveal()->files = $fileBag->reveal();

        $uploadedFile = $this->createUploadedFile(__DIR__ . '/import.csv');

        $fileBag->has('redirectRoutes')->willReturn(true);
        $fileBag->get('redirectRoutes')->willReturn($uploadedFile);

        $import = $this->prophesize(FileImportInterface::class);
        $import->import(Argument::any())->willThrow(ReaderNotFoundException::class);

        $controller = new RedirectRouteImportController($import->reveal(), $this->importPath);
        $response = $controller->postAction($request->reveal(), $this->importPath);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testImportActionConverterNotFound()
    {
        $request = $this->prophesize(Request::class);

        $fileBag = $this->prophesize(FileBag::class);
        $request->reveal()->files = $fileBag->reveal();

        $uploadedFile = $this->createUploadedFile(__DIR__ . '/import.csv');

        $fileBag->has('redirectRoutes')->willReturn(true);
        $fileBag->get('redirectRoutes')->willReturn($uploadedFile);

        $import = $this->prophesize(FileImportInterface::class);
        $import->import(Argument::any())->willThrow(ConverterNotFoundException::class);

        $controller = new RedirectRouteImportController($import->reveal(), $this->importPath);
        $response = $controller->postAction($request->reveal(), $this->importPath);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testImportActionNoFile()
    {
        $request = $this->prophesize(Request::class);

        $fileBag = $this->prophesize(FileBag::class);
        $request->reveal()->files = $fileBag->reveal();

        $fileBag->has('redirectRoutes')->willReturn(false);

        $import = $this->prophesize(FileImportInterface::class);
        $import->import(Argument::any())->shouldNotBeCalled();

        $controller = new RedirectRouteImportController($import->reveal(), $this->importPath);
        $response = $controller->postAction($request->reveal(), $this->importPath);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    private function createUploadedFile(string $filePath): UploadedFile
    {
        $tempFilePath = \tempnam(\sys_get_temp_dir(), 'sulu_redirect_uploaded_');

        if (!$tempFilePath) {
            throw new \RuntimeException(\sprintf('Could not create temporary image in "%s".', __CLASS__));
        }

        \file_put_contents($tempFilePath, \file_get_contents($filePath));

        $uploadedFile = new UploadedFile($tempFilePath, 'import.csv', null, null, true);

        return $uploadedFile;
    }
}
