<?php

namespace Wind\Routing\Exception\Response;

use Exception;
use Wind\Routing\Exception\ResponseException;

class PreconditionRequiredException extends ResponseException
{
    /**
     * Constructor
     *
     * @param string $message
     * @param \Exception $previous
     * @param integer $code
     */
    public function __construct($message = 'Precondition Required', Exception $previous = null, $code = 0)
    {
        parent::__construct(428, $message, $previous, [], $code);
    }
}
