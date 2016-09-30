<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/23
 * Time: 11:08
 */

namespace Wind\Routing\Exception\Dispatcher;

use Exception;
use RuntimeException;
use Wind\Routing\Exception\DispatcherExceptionInterface;

class UnexpectedDispatcherException extends RuntimeException implements DispatcherExceptionInterface
{
    /**
     * NotFoundException constructor.
     * @param string $name
     * @param string $message
     * @param Exception $previous
     * @param int $code
     */
    public function __construct(
        $name,
        $message = "must implement interface \\Wind\\Routing\\Dispatcher\\DispatcherInterface",
        Exception $previous = null, $code = 0
    )
    {
        parent::__construct(sprintf('Response Dispatcher %s ' . $message, $name), $code, $previous);
    }
}
