<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Import\Converter;

use Sulu\Bundle\RedirectBundle\Import\ImportException;

/**
 * This exception will be raised if no converter was found which supports the data-structure.
 */
class ConverterNotFoundException extends ImportException
{
    /**
     * @var array<string, mixed>
     */
    private $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct(sprintf('Data %s is not supported', json_encode($data)));

        $this->data = $data;
    }

    /**
     * Returns data.
     *
     * @return array<string, mixed>
     */
    public function getData()
    {
        return $this->data;
    }
}
