<?php

namespace Wind\Routing\Exception\Response;

use Exception;
use Wind\Routing\Exception\ResponseException;

class ConflictException extends ResponseException
{
    /**
     * Constructor
     *
     * @param string $message
     * @param \Exception $previous
     * @param integer $code
     */
    public function __construct($message = 'Conflict', Exception $previous = null, $code = 0)
    {
        parent::__construct(409, $message, $previous, [], $code);
    }
}
