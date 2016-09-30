<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/19
 * Time: 17:36
 */

namespace Wind\Routing;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Handle
{
    public function __construct($handle, $verify)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {

    }

}
