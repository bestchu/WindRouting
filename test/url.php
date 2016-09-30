<?php
/**
 * Created by PhpStorm.
 * User: newi
 * Date: 2016/9/26
 * Time: 12:46
 */
include __DIR__.'/../vendor/autoload.php';
$router=new \Wind\Routing\Router();
$router
    ->map('/article','article')
    ->map('/list-<lid regex="\d+"/>','list')
    ->map('/<aid regex="\d+"/>','show');
var_dump((string)$router->uri('article'));//=>/article
var_dump((string)$router->uri('article.list',['lid'=>1]));//=>/article
var_dump((string)$router->uri('article.list.show',['lid'=>1,'aid'=>5]));//=>/article