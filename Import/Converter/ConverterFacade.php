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

/**
 * Aggregates multiple converters.
 */
class ConverterFacade implements ConverterInterface
{
    /**
     * @var ConverterInterface[]
     */
    private $converters;

    /**
     * @param ConverterInterface[] $converters
     */
    public function __construct(array $converters = [])
    {
        $this->converters = $converters;
    }

    public function convert(array $item)
    {
        foreach ($this->converters as $converter) {
            if ($converter->supports($item)) {
                return $converter->convert($item);
            }
        }

        return null;
    }

    public function supports(array $item)
    {
        foreach ($this->converters as $converter) {
            if ($converter->supports($item)) {
                return true;
            }
        }

        return false;
    }
}
