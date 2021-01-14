<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Model;

/**
 * interface for redirect route.
 */
interface RedirectRouteInterface
{
    /**
     * Returns uuid.
     *
     * @return string
     */
    public function getId();

    /**
     * Set id.
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Returns enabled.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Set enabled.
     *
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled);

    /**
     * Returns statusCode.
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * Set statusCode.
     *
     * @param int $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode);

    /**
     * Returns source.
     *
     * @return string
     */
    public function getSource();

    /**
     * Set source.
     *
     * @param string $source
     *
     * @return $this
     */
    public function setSource($source);

    /**
     * Set source host.
     *
     * @param string $sourceHost|null
     *
     * @return $this
     */
    public function setSourceHost($sourceHost);

    /**
     * Returns source host.
     *
     * @return string|null
     */
    public function getSourceHost();

    /**
     * Returns target.
     *
     * @return string
     */
    public function getTarget();

    /**
     * Set target.
     *
     * @param string $target
     *
     * @return $this
     */
    public function setTarget($target);
}
