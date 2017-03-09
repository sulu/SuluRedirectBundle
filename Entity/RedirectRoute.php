<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Entity;

use Ramsey\Uuid\Uuid;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;

/**
 * Basic implementation of redirect-route.
 */
class RedirectRoute implements RedirectRouteInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $target;

    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }
}
