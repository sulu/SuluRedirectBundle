<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sulu\Bundle\RedirectBundle\Import\Converter\Converter;
use Sulu\Bundle\RedirectBundle\Import\Converter\ConverterFacade;
use Sulu\Bundle\RedirectBundle\Import\FileImport;
use Sulu\Bundle\RedirectBundle\Import\Reader\CsvReader;
use Sulu\Bundle\RedirectBundle\Import\Reader\ReaderFacade;
use Sulu\Bundle\RedirectBundle\Import\Writer\Writer;
use Symfony\Component\DependencyInjection\Reference;

return static function(ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sulu_redirect.import.aggregate_reader', ReaderFacade::class)
        ->args([
            tagged_iterator('sulu_redirect.import.reader'),
        ]);

    $services->set('sulu_redirect.import.csv_reader', CsvReader::class)
        ->tag('sulu_redirect.import.reader');

    $services->set('sulu_redirect.import.aggregate_converter', ConverterFacade::class)
        ->args([
            tagged_iterator('sulu_redirect.import.converter'),
        ]);

    $services->set('sulu_redirect.import.converter', Converter::class)
        ->args([
            new Reference('sulu.repository.redirect_route'),
        ])
        ->tag('sulu_redirect.import.converter');

    $services->set('sulu_redirect.import.writer', Writer::class)
        ->args([
            new Reference('sulu_redirect.redirect_route_manager'),
            new Reference('doctrine.orm.entity_manager'),
        ]);

    $services->set('sulu_redirect.import', FileImport::class)
        ->public()
        ->args([
            new Reference('sulu_redirect.import.aggregate_reader'),
            new Reference('sulu_redirect.import.aggregate_converter'),
            new Reference('sulu_redirect.import.writer'),
        ]);
};
