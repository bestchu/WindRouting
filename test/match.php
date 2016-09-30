<?php
/**
 * Created by PhpStorm.
 * User: newi
 * Date: 2016/9/26
 * Time: 13:00
 */
include __DIR__.'/../vendor/autoload.php';
$router=new \Wind\Routing\Router();
$router
    ->map('/article','article')
    ->map('/list-<lid regex="\d+"/>','list')
    ->map('/<aid regex="\d+"/>','show');
$url=new \Zend\Diactoros\Uri('http://www.site.com/article');
var_dump((bool)$router->match($url));//true
var_dump((bool)$router->match($url->withPath('/article/list-67')));//true