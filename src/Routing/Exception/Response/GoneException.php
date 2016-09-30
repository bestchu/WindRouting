<?php

namespace Wind\Routing\Exception\Response;

use Exception;
use Wind\Routing\Exception\ResponseException;

class GoneException extends ResponseException
{
    /**
     * Constructor
     *
     * @param string $message
     * @param \Exception $previous
     * @param integer $code
     */
    public function __construct($message = 'Gone', Exception $previous = null, $code = 0)
    {
        parent::__construct(410, $message, $previous, [], $code);
    }
}
