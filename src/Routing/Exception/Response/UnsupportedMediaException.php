<?php

namespace Wind\Routing\Exception\Response;

use Exception;
use Wind\Routing\Exception\ResponseException;

class UnsupportedMediaException extends ResponseException
{
    /**
     * Constructor
     *
     * @param string $message
     * @param \Exception $previous
     * @param integer $code
     */
    public function __construct($message = 'Unsupported Media', Exception $previous = null, $code = 0)
    {
        parent::__construct(415, $message, $previous, [], $code);
    }
}
