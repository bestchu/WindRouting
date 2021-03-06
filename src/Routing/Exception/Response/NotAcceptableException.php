<?php

namespace Wind\Routing\Exception\Response;

use Exception;
use Wind\Routing\Exception\ResponseException;

class NotAcceptableException extends ResponseException
{
    /**
     * Constructor
     *
     * @param string $message
     * @param \Exception $previous
     * @param integer $code
     */
    public function __construct($message = 'Not Acceptable', Exception $previous = null, $code = 0)
    {
        parent::__construct(406, $message, $previous, [], $code);
    }
}
