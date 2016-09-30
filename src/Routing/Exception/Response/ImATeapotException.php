<?php

namespace Wind\Routing\Exception\Response;

use Exception;
use Wind\Routing\Exception\ResponseException;

class ImATeapotException extends ResponseException
{
    /**
     * Constructor
     *
     * @param string $message
     * @param \Exception $previous
     * @param integer $code
     */
    public function __construct($message = 'I\'m a teapot', Exception $previous = null, $code = 0)
    {
        parent::__construct(418, $message, $previous, [], $code);
    }
}
