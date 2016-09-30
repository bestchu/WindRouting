<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/1
 * Time: 15:35
 */

namespace Wind\Routing;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Wind\Routing\Dispatch\DispatcherInterface;
use Wind\Routing\Exception\Dispatcher\NotFoundDispatcherException as DispatcherNotFoundException;
use Wind\Routing\Exception\Dispatcher\UnexpectedDispatcherException;
use Wind\Routing\Exception\Response\MethodNotAllowedException;
use Wind\Routing\Exception\Response\PreconditionRequiredException;
use Wind\Routing\Exception\Route\NotFoundPatternException as RouteNotFoundPatternException;
use Wind\Routing\Exception\Route\UnexpectedParamException as RouteUnexpectedParamException;

class Route
{
    /**
     * @var Router
     */
    protected $router;
    /**
     * 子级路由容器
     * @var RouteCollection
     */
    protected $children;
    /**
     * @var RouteCollection
     */
    protected $parent;
    /**
     * @var array
     */
    protected $handler = [];
    /**
     * 路由规则结构
     * @var array
     */
    protected $routeStructure = [];
    /**
     * 路由规则选项
     * @var array
     */
    protected $routeOption =
        [
            'format' => [],//支持的后缀响应格式
            'dispatch' => '',//默认调度器
            'https' => false,//支持https
            'port' => [],//支持的端口
            'filter' => []//路由过滤器
        ];
    /**
     * 路由规则格式
     * @var string
     */
    protected $routeFormat = '';
    /**
     * 路由中的参数
     * @var array
     */
    protected $routeParams = [];
    /**
     * 匹配的路由参数
     * @var array
     */
    protected $matchParams = [];
    /**
     * 匹配的正则表达式
     * @var string
     */
    protected $matchPattern = '';
    /**
     * 路由规则名称,不能包含.
     * @var string
     */
    protected $name = '';

    /**
     * Route constructor.
     * @param Router $router
     * @param RouteCollection $parent
     * @param string $uri
     * @param string $name
     * @param array $option 路由选项
     */
    public function __construct(Router $router, RouteCollection $parent, $uri, $name = '', array $option = [])
    {
        $this->router = $router;
        $uri = sprintf('/%s', ltrim($uri, '/'));
        list($this->routeStructure, $this->routeFormat) = RouteParse::parse($uri);
        $name = $name ?: md5($this->routeFormat);
        $this->parent = $parent->add($this, $name);
        $this->name = $this->parent->getName() ? $this->parent->getName() . '.' . $name : $name;
        $this->routeOption = array_merge($this->routeOption, $option);
    }

    /**
     * 路由处理程序
     * @param string|array $method 支持的事件方法
     * @param callable|string $handle 处理方法
     * @param array $option 处理方法选项
     * @return Route
     */
    public function handle($method, $handle, array $option = [])
    {
        $methods = array_change_key_case((array)$method, CASE_UPPER);
        $handler = compact('handle', 'option');
        foreach ($methods as $method) {
            $this->handler[$method] =& $handler;
        }
        return $this;
    }

    /**
     * 获取响应的调度器
     * @return DispatcherInterface
     */
    public function getDispatcher()
    {
        //获取响应的调度器
        $dispatchName = $this->getMatchResponseDispatcher();
        if (!$dispatchName) {
            throw new DispatcherNotFoundException($dispatchName);
        }
        $dispatchClass = $this->router->getResponseDispatcher($dispatchName);
        if (!is_subclass_of($dispatchClass, DispatcherInterface::class)) {
            throw new UnexpectedDispatcherException($dispatchClass);
        }
        return new $dispatchClass($this);
    }

    public function dispatch(ServerRequestInterface $request, ResponseInterface $response)
    {

        //        if ($route) {
//            //已匹配路由
//            return $route->dispatch($request, $response);
//        } else {
//            //未匹配路由
//            $route = $this->getErrorRoute(404);
//
//            $responseFormat = pathinfo($uri->getPath(), PATHINFO_EXTENSION);
//            if ($this->hasResponseDispatcher($responseFormat)) {
//                //获取响应调度器
//                $dispatch = $this->getResponseDispatcher($responseFormat);
//            } else {
//                //根据request来判断响应的调度器
//            }
//
//        }
//        //如果未匹配路由则根据url后缀自动判断选择
//
//        $handle = $route->dispatch($request, $response);
//        $dispatcher = $this->getResponseDispatcher($route->getMatchResponseDispatcher());
//        return $dispatcher->dispatch($handle, $request, $response);
        //获取调度器
        $dispatcher = $this->getDispatcher();

        $method = $request->getMethod();
        //匹配方法
        if (array_key_exists($method, $this->handler)) {
            $handler = $this->handler[$method];
        } elseif (array_key_exists('*', $this->handler)) {
            $handler = $this->handler['*'];
        } else {
            $methods = array_keys($this->handler);
            if ($key = array_search('*', $methods)) {
                unset($methods[$key]);
            }
            throw new MethodNotAllowedException($methods);
        }
        $dispatcher->setHandle($handler['handle'],$handler['option']);
        return $dispatcher->dispatch($request,$response);
    }

    /**
     * 获取路由支持的响应格式
     * @return array
     */
    public function getFormats()
    {
        if ($this->routeOption['format']) {
            if (is_string($this->routeOption['format'])) {
                return [$this->routeOption['format']];
            }
            $formats = [];
            foreach ((array)$this->routeOption['format'] as $key => $item) {
                $formats[] = is_int($key) ? $item : $key;
            }
            return $formats;
        }
        return [];
    }

    /**
     * 获取路由规则的正则表达式
     * @return string
     */
    protected function getMatchPattern()
    {
        if ($this->matchPattern) return $this->matchPattern;
        $pattern = '';
        $loopSize = count($this->routeStructure);
        $loopIndex = 1;
        foreach ($this->routeStructure as $k => $v) {
            if (is_array($v)) {
                $regex = '';
                if (array_key_exists('regex', $v)) {
                    $regex = $v['regex'];
                } else {
                    try {
                        $regex = $this->router->getPattern($k)->getRegex();
                    } catch (RouteNotFoundPatternException $e) {

                    }
                }
                $isDefault = array_key_exists('default', $v);
                $isDefault or $v['default'] = '';;
                $this->routeParams[$k] = $v;
                if (!strlen($regex)) {
                    switch ($k) {
                        case 'module':
                            $regex = '\w+';
                            break;
                        case 'controller':
                        case 'action':
                            $regex = '[a-zA-Z]\w?';
                            break;
                        case 'format':
                            $formats = $this->getFormats() ?: $this->router->getResponseFormats();
                            $regex = $formats ? '\.(' . implode('|', $formats) . ')' : '\.\w+';
                            break;
                        default:
                            $regex = '[^\s\/]+';
                            break;
                    }
                }
                $pattern .= sprintf('(?P<%s>%s)%s', $k, $regex, $isDefault ? '?' : '');
            } else {
                $pattern .= Pattern::quoteRegex(
                    $loopIndex == $loopSize && $this->router->isRemoveExtraSlashes() ? rtrim($v) : $v
                );
            }
            $loopIndex++;
        }
        //正则表达式
        return $this->matchPattern =
            Pattern::formatRegex(
                sprintf('^%s', $pattern),
                //正则匹配时不区分大小写
                $this->router->isCaseSensitive() ? '' : 'i'
            );
    }

    /**
     * 设置匹配的路由参数
     * @param array $params
     * @return Route
     */
    public function withMathParams(array $params)
    {
        $this->matchParams = $params;
        return $this;
    }


    /**
     * 获取匹配的路由规则
     * @param string $part uri片段
     * @return Route|array|bool
     */
    public function match($part)
    {
        if (!preg_match($this->getMatchPattern(), $part, $matches)) return false;
        $part = substr($part, strlen(array_shift($matches)));
        $params = [];
        if ($matches) {
            //进行参数验证
            foreach ($this->routeParams as $k => $v) {
                $params[$k] = array_key_exists($k, $matches) && strlen($matches[$k]) ? $matches[$k] : $v['default'];
                try {
                    $pattern = $this->router->getPattern($k);
                    if (!$pattern->getRegex()) {
                        if (!$pattern($params[$k], $k)) {
                            throw new RouteUnexpectedParamException(sprintf('The requested router(%s) parameter (%s) validation error', $this->getName(), $k));
                        }
                    }
                } catch (RouteNotFoundPatternException $e) {

                }
            }
        }
        //去除url末尾斜杠
        if($part=='/'&&$this->router->isRemoveExtraSlashes()){
            $part='';
        }
        if (strlen($part) === 0) {
            return $params;
        }
        $children = $this->getChildren();
        if ($children->count()) {
            return $children->match($part, $params);
        }
        return false;
    }

    /**
     * 获取当前匹配的路由参数
     * @return array
     */
    public function getMatchParams()
    {
        return $this->matchParams;
    }

    /**
     * 获取路由参数
     * @param string $name 参数名称
     * @param mixed $default 默认值
     * @return mixed|null
     */
    public function getMatchParam($name, $default = null)
    {
        if (array_key_exists($name, $this->matchParams)) {
            return $this->matchParams[$name];
        } elseif (
            array_key_exists($name, $this->routeStructure) &&
            array_key_exists('default', $this->routeStructure[$name])
        ) {
            return $this->routeStructure[$name]['default'];
        }
        return $default;
    }

    /**
     * 获取响应的调度器名称
     * @return string
     */
    public function getMatchResponseDispatcher()
    {
        $format = $this->getMatchParam('format');

        if (!$format) {
            return $this->routeOption['dispatch'] ?: $this->router->getDefaultResponseDispatcher();
        }
        if (
            is_array($this->routeOption['format']) &&
            array_key_exists($format, $this->routeOption['format'])
        ) {
            return $this->routeOption['format'][$format];
        }
        return $format;
    }

    /**
     * 获取当前路由格式
     * @return array
     */
    public function getRouteFormat()
    {
        return $this->routeFormat;
    }

    /**
     * 增加路由规则
     * @param string $uri 路由规则
     * @param string $name 路由规则名称
     * @return Route
     */
    public function map($uri, $name = '')
    {
        return new static($this->router, $this->getChildren(), $uri, $name);
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
     * 路由生成规则片段
     * @param array $params 路由参数
     * @return string
     */
    public function toPart(array &$params = [])
    {
        $uri = '';
        foreach ($this->routeStructure as $k => $v) {
            if (is_array($v)) {
                if (array_key_exists($k, $params)) {
                    $uri .= $params[$k];
                    unset($params[$k]);
                }
                continue;
            }
            $uri .= $v;
        }
        return $uri;
    }

    /**
     * 获取当前路由下的子级路由节点
     * @return RouteCollection
     */
    public function getChildren()
    {
        $this->children or $this->children = new RouteCollection(
            Router::MAP_PATH,
            $this->getName()
        );
        return $this->children;
    }
}
