<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Entity;

use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * Basic implementation of redirect-route.
 */
class RedirectRoute implements RedirectRouteInterface, AuditableInterface
{
    use AuditableTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var int
     */
    protected $statusCode = 301;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string|null
     */
    protected $sourceHost;

    /**
     * @var string
     */
    protected $target;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource($source)
    {
        $this->source = mb_strtolower('/' . ltrim($source, '/'));

        return $this;
    }

    public function getSourceHost()
    {
        return $this->sourceHost;
    }

    public function setSourceHost($sourceHost)
    {
        $this->sourceHost = empty($sourceHost) ? null : mb_strtolower($sourceHost);

        return $this;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }
}
