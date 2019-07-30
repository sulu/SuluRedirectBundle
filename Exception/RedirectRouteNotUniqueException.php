<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Exception;

/**
 * Redirect-route for given source already exists.
 */
class RedirectRouteNotUniqueException extends \Exception
{
    /**
     * @var string
     */
    private $source;

    /**
     * @param string $source
     */
    public function __construct($source)
    {
        parent::__construct(sprintf('The source "%s" is already in use.', $source));

        $this->source = $source;
    }

    /**
     * Returns source which is not unique.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }
}
