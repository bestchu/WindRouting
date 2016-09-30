<?php

namespace Wind\Routing\Exception\Response;

use Exception;
use Wind\Routing\Exception\ResponseException;

class MethodNotAllowedException extends ResponseException
{
    /**
     * Constructor
     *
     * @param array $allowed
     * @param string $message
     * @param \Exception $previous
     * @param integer $code
     */
    public function __construct(array $allowed = [], $message = 'Method Not Allowed', Exception $previous = null, $code = 0)
    {
        $headers = [
            'Allow' => implode(', ', $allowed)
        ];

        parent::__construct(405, $message, $previous, $headers, $code);
    }
}
