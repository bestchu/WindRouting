<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/13
 * Time: 15:46
 */

namespace Wind\Routing\Exception\Route;

use RuntimeException;
use Wind\Routing\ExceptionInterface;

abstract class NotFoundException extends RuntimeException implements ExceptionInterface
{

}
