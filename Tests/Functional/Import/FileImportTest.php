<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Functional\Import;

use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class FileImportTest extends SuluTestCase
{
    public function testImport()
    {
        $this->purgeDatabase();

        /** @var RedirectRouteRepositoryInterface $repository */
        $repository = $this->getContainer()->get('sulu.repository.redirect_route');

        $fileName = __DIR__ . '/Reader/import.csv';
        $import = $this->getContainer()->get('sulu_redirect.import');

        $sources = [];
        foreach ($import->import($fileName) as $item) {
            $sources[] = $item->getData()->getSource();
        }

        foreach ($sources as $source) {
            $this->assertNotNull($repository->findBySource($source));
        }
    }
}
