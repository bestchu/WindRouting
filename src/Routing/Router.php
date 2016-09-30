<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/16
 * Time: 11:24
 */

namespace Wind\Routing;


use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Wind\Routing\Dispatch\DispatcherInterface;
use Wind\Routing\Exception\Dispatcher\NotFoundDispatcherException as DispatcherNotFoundException;
use Wind\Routing\Exception\Route\NotFoundPatternException as RouteNotFoundPatternException;
use Zend\Diactoros\Uri;

class Router
{
    //匹配主机
    const MAP_HOST = 'DOMAIN';
    //匹配路径
    const MAP_PATH = 'PATH';
    //未匹配到路径
    const EVENT_NOT_PATH = '_NOT_PATH_';
    //为匹配到主机
    const EVENT_NOT_HOST = '_NOT_HOST_';
    /**
     * 路由容器
     * @var RouteCollection
     */
    protected $routeCollection;
    /**
     * 错误路由容器
     * @var RouteCollection
     */
    protected $errorRouteCollection;
    /**
     * 路由参数验证规则
     * @var array
     */
    protected $patterns = [];
    /**
     * 是否区分大小写
     * @var bool
     */
    protected $caseSensitive = true;
    /**
     * 是否去除默认斜杠
     * @var bool
     */
    protected $removeExtraSlashes = true;
    /**
     * 路由支持的响应调度器
     * @var array
     */
    protected $responseDispatcher = [];
    /**
     * 路由响应调度器对应的后缀
     * @var array
     */
    protected $responseFormats = [];
    /**
     * 设置默认响应调度器
     * @var string
     */
    protected $defaultResponseDispatcher = '';

    /**
     * Router constructor.
     * @param bool $caseSensitive 是否区分大小写
     * @param bool $removeExtraSlashes 是否去除默认的斜线
     * @param string $mapType 路由第一级匹配类型
     */
    public function __construct($caseSensitive = true, $removeExtraSlashes = true, $mapType = self::MAP_PATH)
    {
        $this->caseSensitive = (bool)$caseSensitive;
        $this->removeExtraSlashes = (bool)$removeExtraSlashes;
        $this->routeCollection = new RouteCollection($mapType);
        $this->errorRouteCollection = new RouteCollection();
    }

    /**
     * 设置或获取路由参数验证规则
     * @param string $name
     * @param string|callable $pattern
     * @return $this
     */
    public function pattern($name, $pattern)
    {
        $this->patterns[$name] = new Pattern($pattern);
        return $this;
    }

    /**
     * 去除url末尾斜杠
     * @return bool
     */
    public function isRemoveExtraSlashes()
    {
        return $this->removeExtraSlashes;
    }

    /**
     * 正则匹配时不区分大小写
     * @return bool
     */
    public function isCaseSensitive()
    {
        return $this->caseSensitive;
    }

    /**
     * 获取路由参数验证规则
     * @param string $name
     * @return Pattern
     * @throws RouteNotFoundPatternException
     */
    public function getPattern($name)
    {
        if (array_key_exists($name, $this->patterns)) {
            return $this->patterns[$name];
        }
        throw new RouteNotFoundPatternException(sprintf('No pattern of the name (%s) exists', $name));
    }

    /**
     * 添加路由到指定路由节点
     * @param string $name 获取路由节点名称
     * @param callable $callable 回调函数
     * @return Route
     * @throws \Wind\Routing\Exception\Route\NotFoundRouteException
     */
    public function append($name, callable $callable)
    {
        return $callable($this->getRoute($name));
    }

    /**
     * 获取定义的路由规则
     * @param string $name
     * @return Route
     * @throws \Wind\Routing\Exception\Route\NotFoundRouteException
     */
    public function getRoute($name)
    {
        $index = explode('.', $name);
        $col = $this->getRouteCollection();
        foreach ($index as $name) {
            if ($col instanceof Route) {
                $col = $col->getChildren();
            }
            if ($col instanceof RouteCollection) {
                $col = $col->get($name);
            }
        }
        return $col;
    }

    /**
     * 获取错误状态路由容器
     * @param string $code
     * @return Route
     */
    public function getErrorRoute($code)
    {
        return $this->getErrorRouteCollection()->get($code);
    }

    /**
     * 匹配路由规则
     * @param string $uri 路由规则
     * @param string $name 路由节点名称
     * @param array $option 路由选项 ['response'=>['html','json','xml'],'https'=>true,'port'=>[80],'filter'=>[]]
     * @return Route
     */
    public function map($uri, $name = '', array $option = [])
    {
        return new Route($this, $this->getRouteCollection(), $uri, $name, $option);
    }

    /**
     * 匹配错误路由
     * @param string $code 错误代码
     * @param callable|string $handle 处理程序
     * @return Route
     */
    public function mapError($code, $handle)
    {
        $route = new Route($this, $this->getErrorRouteCollection(), $code, $code);
        return $route->handle('*', $handle);
    }

    /**
     * 设置不同响应格式对应的调度器
     * @param string $name 调度器名称
     * @param DispatcherInterface $className 调度器class
     * @param string|array $format 格式后缀，以小写表示，比如html,json,jsonp,xml等，
     * @return $this
     */
    public function setResponseDispatcher($name, $className, $format = null)
    {
        $this->responseDispatcher[$name] = $className;
        if ($format) {
            foreach ((array)$format as $item) {
                $this->responseFormats[$item] = $className;
            }
        }
        return $this;
    }

    /**
     * 获取路由响应调度器对应的格式
     * @return array
     */
    public function getResponseFormats()
    {
        return array_keys($this->responseFormats);
    }

    /**
     * 设置默认的响应调度器
     * @param string $name 格式后缀，以小写表示;
     * @return Router
     * @throws DispatcherNotFoundException
     */
    public function setDefaultResponseDispatcher($name)
    {
        $this->getResponseDispatcher($name);
        $this->defaultResponseDispatcher = $name;
        return $this;
    }

    /**
     * 获取路由响应调度器
     * @param string $name 调度器名称
     * @return DispatcherInterface
     * @throws DispatcherNotFoundException
     */
    public function getResponseDispatcher($name)
    {
        if ($this->hasResponseDispatcher($name)) {
            return $this->responseDispatcher[$name];
        }
        throw new DispatcherNotFoundException($name);
    }

    /**
     * 判断响应调度器是否存在
     * @param string $name
     * @return bool
     */
    public function hasResponseDispatcher($name)
    {
        return array_key_exists($name, $this->responseDispatcher);
    }

    /**
     * 获取默认的响应调度器名称
     * @return string
     */
    public function getDefaultResponseDispatcher()
    {
        return $this->defaultResponseDispatcher;
    }

    /**
     * 获取路由容器
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->routeCollection;
    }

    /**
     * 获取错误状态路由容器
     * @return RouteCollection
     */
    public function getErrorRouteCollection()
    {
        return $this->errorRouteCollection;
    }

    /**
     * 根据request进行路由调度
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response = null)
    {
        //当前响应的url
        $uri = $request->getUri();
        //获取响应的路由规则
        $route = $this->match($uri) ?: $this->getErrorRoute(404);
        //进行路由调度
        return $route->dispatch($request, $response);
    }

    /**
     * 根据uri匹配路由规则
     * @param UriInterface $uri
     * @return bool|Route
     */
    public function match(UriInterface $uri)
    {
        $col = $this->getRouteCollection();
        $col =
            $col->getType() == self::MAP_HOST ?
                $col->match($uri->getHost()) :
                $col;
        return $col->match($uri->getPath());
    }

    /**
     * 生成url
     * @param string $name 路由节点名称
     * @param array $params 路由节点参数
     * @param UriInterface $uri 默认url
     * @return UriInterface
     */
    public function uri($name, array $params = [], UriInterface $uri = null)
    {
        $index = explode('.', $name);
        $col = $this->getRouteCollection();
        if (!$uri) $uri = new Uri();
        if ($col->getType() == self::MAP_HOST) {
            $col = $col->get(array_shift($index));
            $uri = $uri->withHost($col->toPart($params));
        }
        $part = '';
        foreach ($index as $name) {
            if ($col instanceof RouteCollection) {
                $col = $col->get($name);
            }
            if ($col instanceof Route) {
                $part .= $col->toPart($params);
                $col = $col->getChildren();
            }
        }
        $uri = $uri->withPath($part);
        return $params ? $uri->withQuery(http_build_query($params)) : $uri;
    }

    /**
     * 生成url
     * @param string $name 路由节点名称
     * @param array $params 路由节点参数
     * @param UriInterface $uri 默认url
     * @return UriInterface
     */
    public function __invoke($name, array $params = [], UriInterface $uri = null)
    {
        return $this->uri($name, $params, $uri);
    }
}
