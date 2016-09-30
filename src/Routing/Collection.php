<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/1
 * Time: 15:29
 */

namespace Wind\Routing;

use IteratorAggregate;
use Countable;
use ArrayIterator;
use Wind\Routing\Exception\Route\NotFoundRouteException as RouteNotFoundRouteException;

class Collection implements IteratorAggregate, Countable
{
    /**
     * 路由集合
     * @var array
     */
    protected $routes = [];
    /**
     * @var string 路由匹配类型
     */
    protected $mapType;
    /**
     * @var string 路由规则名称,不能包含.
     */
    protected $name;

    /**
     * Collection constructor.
     * @param string $mapType
     * @param string $name
     */
    public function __construct($mapType = Router::MAP_PATH, $name = '')
    {
        $this->mapType = $mapType;
        $this->name = $name;
    }

    /**
     * 添加路由节点
     * @param Route $route
     * @param string $name
     * @return $this
     */
    public function add(Route $route, $name = '')
    {
        $this->routes[$name] = $route;
        return $this;
    }

    /**
     * 获取匹配的路由类型
     * @return string
     */
    public function getType()
    {
        return $this->mapType;
    }

    /**
     * 获取路由节点名称
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取定义的路由规则
     * @param string $name
     * @return Route
     * @throws \Wind\Routing\Exception\Route\NotFoundRouteException
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->routes)) return $this->routes[$name];
        throw new RouteNotFoundRouteException(sprintf('No route of the name (%s) exists', $this->name . '.' . $name));
    }

    /**
     * 获取匹配的路由规则
     * @param string $part uri片段
     * @return bool|Route
     */
    public function match($part)
    {
        $params=func_num_args()>1?func_get_arg(1):'';
        foreach ($this->routes as $route) {
            if($route instanceof Route){
                $res=$route->match($part);
                if(is_array($res)){
                    return $route->withMathParams(is_array($params)&&$params?array_merge($params,$res):$res);
                }elseif($res instanceof Route){
                    return $route;
                }
            }
        }
        return false;
    }

    /**
     * 返回迭代对象
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->routes);
    }

    /**
     * 获取容器路由节点数量
     * @return int
     */
    public function count()
    {
        return count($this->routes);
    }
}
