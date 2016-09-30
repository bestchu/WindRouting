<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/18
 * Time: 10:59
 */

namespace Wind\Routing\Exception\Route;


use UnexpectedValueException;
use Wind\Routing\ExceptionInterface;

class UnexpectedParamException extends UnexpectedValueException implements ExceptionInterface
{

}
