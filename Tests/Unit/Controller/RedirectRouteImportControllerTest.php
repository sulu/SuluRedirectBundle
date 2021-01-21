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
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RedirectRouteImportControllerTest extends TestCase
{
    /**
     * @var string
     */
    private $importPath = '/var/uploads/imports';

    /**
     * @var string
     */
    private $fileName = 'test.csv';

    public function testImportAction()
    {
        $request = $this->prophesize(Request::class);

        $fileBag = $this->prophesize(FileBag::class);
        $request->reveal()->files = $fileBag->reveal();

        $file = new File($fileName = __DIR__ . '/import.csv');
        $importFile = $file->getPathname();

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([$importFile, $this->fileName, null, null, UPLOAD_ERR_NO_FILE])
            ->getMock();
        $uploadedFile->method('getClientOriginalName')->willReturn($this->fileName);
        $uploadedFile->method('move')->with($this->importPath, $this->fileName)->willReturn($file);
        $fileBag->has('files')->willReturn(true);
        $fileBag->get('files')->willReturn([$uploadedFile]);

        $items = [
            new Item(1, '', $this->prophesize(RedirectRouteInterface::class)->reveal()),
            new Item(2, '', null, $this->prophesize(ImportException::class)->reveal()),
            new Item(3, '', $this->prophesize(RedirectRouteInterface::class)->reveal()),
        ];

        $import = $this->prophesize(FileImportInterface::class);
        $import->import($file->getRealPath())->willReturn($items);

        $controller = new RedirectRouteImportController($import->reveal(), $this->importPath);
        $response = $controller->postAction($request->reveal(), $this->importPath);

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

        $file = new File($fileName = __DIR__ . '/import.csv');
        $importFile = $file->getPathname();

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([$importFile, $this->fileName, null, null, UPLOAD_ERR_NO_FILE])
            ->getMock();

        $uploadedFile->method('getClientOriginalName')->willReturn($this->fileName);
        $uploadedFile->method('move')->with($this->importPath, $this->fileName)->willReturn($file);
        $fileBag->has('files')->willReturn(true);
        $fileBag->get('files')->willReturn([$uploadedFile]);

        $import = $this->prophesize(FileImportInterface::class);
        $import->import($file->getRealPath())->willThrow(ReaderNotFoundException::class);

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

        $file = new File($fileName = __DIR__ . '/import.csv');
        $importFile = $file->getPathname();

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([$importFile, $this->fileName, null, null, UPLOAD_ERR_NO_FILE])
            ->getMock();
        $uploadedFile->method('getClientOriginalName')->willReturn($this->fileName);
        $uploadedFile->method('move')->with($this->importPath, $this->fileName)->willReturn($file);
        $fileBag->has('files')->willReturn(true);
        $fileBag->get('files')->willReturn([$uploadedFile]);

        $import = $this->prophesize(FileImportInterface::class);
        $import->import($file->getRealPath())->willThrow(ConverterNotFoundException::class);

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

        $fileBag->has('files')->willReturn(false);

        $import = $this->prophesize(FileImportInterface::class);
        $import->import(Argument::any())->shouldNotBeCalled();

        $controller = new RedirectRouteImportController($import->reveal(), $this->importPath);
        $response = $controller->postAction($request->reveal(), $this->importPath);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }
}
