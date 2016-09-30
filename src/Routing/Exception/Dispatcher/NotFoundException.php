<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/18
 * Time: 17:53
 */

namespace Wind\Routing\Exception\Dispatcher;

use Exception;
use RuntimeException;
use Wind\Routing\Exception\DispatcherExceptionInterface;


class NotFoundException extends RuntimeException implements DispatcherExceptionInterface
{
    /**
     * 未响应调度器名称
     * @var string
     */
    protected $responseDispatch = '';

    /**
     * NotFoundException constructor.
     * @param string $format
     * @param string $message
     * @param Exception $previous
     * @param int $code
     */
    public function __construct($format, $message = "Not Found", Exception $previous=null, $code = 0)
    {
        $this->responseDispatch = $format;
        parent::__construct(sprintf('Response Dispatcher %s ' . $message, $format), $code, $previous);
    }

    /**
     * 获取未响应调度器名称
     * @return string
     */
    public function getResponseDispatchName()
    {
        return $this->responseDispatch;
    }
}
