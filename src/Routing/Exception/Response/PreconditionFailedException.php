<?php

namespace Wind\Routing\Exception\Response;

use Exception;
use Wind\Routing\Exception\ResponseException;

class PreconditionFailedException extends ResponseException
{
    /**
     * Constructor
     *
     * @param string $message
     * @param \Exception $previous
     * @param integer $code
     */
    public function __construct($message = 'Precondition Failed', Exception $previous = null, $code = 0)
    {
        parent::__construct(412, $message, $previous, [], $code);
    }
}
