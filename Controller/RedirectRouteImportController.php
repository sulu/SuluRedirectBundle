<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Controller;

use Sulu\Bundle\RedirectBundle\Admin\RedirectAdmin;
use Sulu\Bundle\RedirectBundle\Import\Converter\ConverterNotFoundException;
use Sulu\Bundle\RedirectBundle\Import\FileImportInterface;
use Sulu\Bundle\RedirectBundle\Import\ImportException;
use Sulu\Bundle\RedirectBundle\Import\Item;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderNotFoundException;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides API to upload importable files.
 */
class RedirectRouteImportController implements SecuredControllerInterface
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
     * @param string $importPath
     */
    public function __construct(FileImportInterface $import, $importPath)
    {
        $this->import = $import;
        $this->importPath = $importPath;
    }

    public function getSecurityContext(): string
    {
        return RedirectAdmin::SECURITY_CONTEXT;
    }

    public function getLocale(Request $request)
    {
        return $request->get('locale', null);
    }

    /**
     * Import file which was uploaded.
     *
     * @return Response
     */
    public function postAction(Request $request)
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
     * @return array{
     *     fileName: string,
     *     total: int,
     *     exceptions: array<array{
     *         exception: null|ImportException,
     *         lineNumber: int,
     *         lineContent: string,
     *     }>,
     * }
     */
    private function importFile(File $file)
    {
        $response = [
            'fileName' => $file->getFilename(),
            'total' => 0,
            'exceptions' => [],
        ];

        $filePath = $file->getRealPath();

        if (!$filePath) {
            throw new \RuntimeException('Redirect import file does not exist.');
        }

        /** @var Item $item */
        foreach ($this->import->import($filePath) as $item) {
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
