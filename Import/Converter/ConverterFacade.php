<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
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

    /**
     * {@inheritdoc}
     */
    public function convert(array $item)
    {
        foreach ($this->converters as $converter) {
            if ($converter->supports($item)) {
                return $converter->convert($item);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
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
