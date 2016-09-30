<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/12
 * Time: 17:39
 */

namespace Wind\Routing;

use Wind\Routing\Exception\Route\UnexpectedParseRouteException;
use XMLReader;

class RouteParse
{
    /**
     * 解析路由规则
     * @param string $uri
     * @return array
     */
    public static function parse($uri)
    {
        if (false === strpos($uri, '<')) return [[$uri],''];
        //路由规则结构
        $structure = [];
        //格式
        $format='';
        //记录错误
        libxml_use_internal_errors(TRUE);
        $xml = new XMLReader();
        $xml->xml('<route>' . $uri . '</route>', 'utf-8');
        //读取根节点
        $xml->read();
        $tagName = '';
        $isEmpty = false;
        while ($xml->read()) {
            switch ($xml->nodeType) {
                case XMLReader::ELEMENT:
                    if ($tagName && $isEmpty === false) throw new UnexpectedParseRouteException("route format error [$uri]");
                    $tagName = $xml->name;
                    $isEmpty = $xml->isEmptyElement;
                    if ($xml->hasAttributes) {
                        while ($xml->moveToNextAttribute()) {
                            $structure[$tagName][$xml->name] = $xml->value;
                        }
                    } else {
                        $structure[$tagName] = [];
                    }
                    $format.='<'.$tagName.'>';
                    break;
                case XMLReader::TEXT:
                case XMLReader::CDATA:
                    if ($tagName && !$isEmpty) {
                        $structure[$tagName]['default'] = $xml->value;
                        break;
                    }
                    $format.=$xml->value;
                    $structure[] = $xml->value;
                    break;
                case XMLReader::COMMENT:
                    break;
                case XMLReader::END_ELEMENT:
                    $tagName = '';
                    $isEmpty = false;
                    break;
                default:
                    break;
            }
        }
        $e = libxml_get_last_error();
        $xml->close();
        if ($e) throw new UnexpectedParseRouteException("{$e->message} [$uri]");
        return [$structure,$format];
    }
}
