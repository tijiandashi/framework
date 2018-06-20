<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/6/19
 * Time: 下午12:00
 */
namespace Dai\Framework\Library;

class Annotations
{
    /**
     * @param $comment
     * @param $key
     * @param bool $isArray
     * @return array|string
     */
    public static function getCommentValue($comment, $key, $isArray=false)
    {
        //获取url
        preg_match_all( "/@$key\((.*)\)/i", $comment, $matches);
        if( count($matches[1]) == 0 ){
            return "";
        }

        if( $isArray == false ){
            return $matches[1][0];
        }else {
            return $matches[1];
        }
    }
}