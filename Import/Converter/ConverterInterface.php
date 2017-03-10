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

use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;

/**
 * Interface for redirect-route converter.
 */
interface ConverterInterface
{
    /**
     * Convert given raw-item to redirect-route.
     *
     * @param array $item
     *
     * @return RedirectRouteInterface
     */
    public function convert(array $item);

    /**
     * Returns true if the item is supported.
     *
     * @param array $item
     *
     * @return bool
     */
    public function supports(array $item);
}
