<?php

namespace Sulu\Bundle\RedirectBundle\Exception;

use Exception;

/**
 * Redirect-route not found.
 */
class RedirectRouteNotFoundException extends Exception
{
    /**
     * The id of the redirect_route, which was not found.
     *
     * @var int
     */
    protected $id;

    /**
     * @param int $id The id of the entity, which was not found
     */
    public function __construct($id)
    {
        $this->id = $id;
        $message = 'The redirect_route with the id "' . $id . '" was not found.';
        parent::__construct($message, 0);
    }

    /**
     * Returns the id of the redirect_route, which was not found.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
