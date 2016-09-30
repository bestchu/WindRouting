<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/23
 * Time: 10:38
 */

namespace Wind\Routing\Dispatch;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Wind\Routing\Route;

class Dispatcher implements DispatcherInterface
{
    /**
     * @var Route
     */
    protected $route;
    /**
     * @var callable|string
     */
    protected $handle;
    /**
     * @var array
     */
    protected $option;

    /**
     * Dispatcher constructor.
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;

    }

    /**
     *
     * @param callable|string $handle
     * @param array $option
     * @return \Wind\Routing\Dispatcher
     */
    public function setHandle( $handle, array $option = [])
    {
        $this->handle = $handle;
        $this->option = $option;
        return $this;
    }

    /**
     * @return \Closure
     */
    public function getCallable(){
        return function (){};
    }
    /**
     * 进行路由调度
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response = null)
    {
        return $response;
    }

    public function parseHandle($handle)
    {
        if ($handle instanceof \Closure) {
            return $handle;
        }
        if (is_callable($handle)) {
            return $handle;
        }
        if (is_string($handle)) {
            $info = parse_url(strtr($handle, ['::' => '/']));
            if (!$info || count(array_intersect_key($info, ['scheme' => 1, 'host' => 1, 'path' => 1])) != 3) {
                throw new \Exception('handler format error:module://controller::action');
            }
            array_key_exists('query', $info) ? parse_str($info['query'], $info['query']) : $info['query'] = [];
            $info['path'] = trim($info['path'], '/');
            return array_combine(['module', 'controller', 'action', 'param'], $info);
        } else {
            throw new \Exception('handler format error:module://controller::action');
        }

    }

}
