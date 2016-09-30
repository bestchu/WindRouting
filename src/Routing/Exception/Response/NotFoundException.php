<?php

namespace Wind\Routing\Exception\Response;

use Exception;
use Wind\Routing\Exception\ResponseException;

class NotFoundException extends ResponseException
{
    /**
     * Constructor
     *
     * @param string $message
     * @param \Exception $previous
     * @param integer $code
     */
    public function __construct($message = 'Not Found', Exception $previous = null, $code = 0)
    {
        parent::__construct(404, $message, $previous, [], $code);
    }
}
