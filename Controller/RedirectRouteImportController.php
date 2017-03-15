<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Controller;

use Sulu\Bundle\RedirectBundle\Import\Converter\ConverterNotFoundException;
use Sulu\Bundle\RedirectBundle\Import\FileImportInterface;
use Sulu\Bundle\RedirectBundle\Import\Item;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides API to upload importable files.
 */
class RedirectRouteImportController
{
    /**
     * @var FileImportInterface
     */
    private $import;

    /**
     * @var string
     */
    private $importPath;

    /**
     * @param FileImportInterface $import
     * @param string $importPath
     */
    public function __construct(FileImportInterface $import, $importPath)
    {
        $this->import = $import;
        $this->importPath = $importPath;
    }

    /**
     * Import file which was uploaded.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function importAction(Request $request)
    {
        if (!$request->files->has('redirectRoutes')) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('redirectRoutes');
        $file = $uploadedFile->move(
            $this->importPath,
            $uploadedFile->getClientOriginalName()
        );

        try {
            return new JsonResponse($this->importFile($file));
        } catch (ReaderNotFoundException $exception) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        } catch (ConverterNotFoundException $exception) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Import given file and returns serializable response.
     *
     * @param File $file
     *
     * @return array
     */
    private function importFile(File $file)
    {
        $response = [
            'fileName' => $file->getFilename(),
            'total' => 0,
            'exceptions' => [],
        ];

        /** @var Item $item */
        foreach ($this->import->import($file->getRealPath()) as $item) {
            ++$response['total'];

            if (!$item->getException()) {
                continue;
            }

            $response['exceptions'][] = [
                'exception' => $item->getException(),
                'lineNumber' => $item->getLineNumber(),
                'lineContent' => $item->getLineContent(),
            ];
        }

        return $response;
    }
}
