<?php

namespace Wind\Routing\Exception\Response;

use Exception;
use Wind\Routing\Exception\ResponseException;

class TooManyRequestsException extends ResponseException
{
    /**
     * Constructor
     *
     * @param string $message
     * @param \Exception $previous
     * @param integer $code
     */
    public function __construct($message = 'Too Many Requests', Exception $previous = null, $code = 0)
    {
        parent::__construct(429, $message, $previous, [], $code);
    }
}
