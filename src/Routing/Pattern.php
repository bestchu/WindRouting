<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/12
 * Time: 17:47
 */

namespace Wind\Routing;


class Pattern
{
    /**
     * 匹配规则
     * @var callable|string
     */
    protected $pattern;
    /**
     * 是否为正则
     * @var bool
     */
    protected $regex;
    /**
     * @var callable
     */
    protected $handle;

    /**
     * Pattern constructor.
     * @param string|callable $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * 获取匹配正则
     * @return string
     */
    public function getRegex()
    {
        if ($this->regex === null) {
            $this->regex = is_callable($this->pattern) ? false : $this->pattern;
        }
        return $this->regex;
    }

    /**
     * 转义正则表达式
     * @param string $regex
     * @return string
     */
    public static function quoteRegex($regex)
    {
        return preg_quote((string)$regex, '/');
    }

    /**
     * 格式化正则
     * @param string $pattern 正则表达式
     * @param string $modifier 正则修饰符
     * @return string
     */
    public static function formatRegex($pattern, $modifier = '')
    {
        return $pattern?sprintf('/%s/%s', (string)$pattern, (string)$modifier):'';
    }

    /**
     * 获取验证方法
     * @return callable|\Closure|string
     */
    protected function getCallable()
    {
        if ($this->handle) return $this->handle;
        $regex = static::formatRegex($this->getRegex());
        if ($regex) {
            return $this->handle = function ($value) use ($regex) {
                return preg_match($regex, $value);
            };
        }
        return $this->handle = $this->pattern;
    }

    /**
     * 对路由参数进行验证
     * @param string $value
     * @param array $params
     * @return bool
     */
    public function __invoke($value, array &$params)
    {
        return call_user_func($this->getCallable(), $value, $params);
    }
}
