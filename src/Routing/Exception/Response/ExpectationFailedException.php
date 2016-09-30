<?php

namespace Wind\Routing\Exception\Response;

use Exception;
use Wind\Routing\Exception\ResponseException;

class ExpectationFailedException extends ResponseException
{
    /**
     * Constructor
     *
     * @param string $message
     * @param \Exception $previous
     * @param integer $code
     */
    public function __construct($message = 'Expectation Failed', Exception $previous = null, $code = 0)
    {
        parent::__construct(417, $message, $previous, [], $code);
    }
}
