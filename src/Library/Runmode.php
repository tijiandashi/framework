<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:05
 */
namespace Dai\Framework\Library;

class Runmode
{
    public static function  get()
    {
        $res = get_cfg_var("run.mode");
        if($res !== false){
            return $res;
        }else {
            return "online";
        }
    }
}