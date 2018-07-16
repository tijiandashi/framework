<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:05
 */
namespace Dai\Framework\Library;

use Phalcon\Logger;


/**
 * Class Log
 * @package Dai\Framework\Library
 * SPECIAL 9
 * CUSTOM 8
 * DEBUG 7
 * INFO 6
 * NOTICE 5
 * WARNING 4
 * ERROR 3
 * ALERT 2
 * CRITICAL 1
 * EMERGENCE 0
 * EMERGENCY 0
 */
class Log
{
    private static $_instances = [];

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        $di = \Phalcon\DI::getDefault();

        if( $di != null) {
            /** @var \Dai\Framework\Base\BasePageInfo $basePageInfo */
            $basePageInfo = $di->get('basePageInfo');
            $module = lcfirst($basePageInfo->module);
        }else {
            $module = "default";
        }

        if( ! isset( self::$_instances[$module] ) ) {
            $logConfig = ConfigLibrary::get("config", "log");
            $filePath = APP_PATH.self::getConfig($logConfig,"filePath", "/log");
            $level = self::getConfig($logConfig,"level", 4);
            $format = self::getConfig($logConfig,"format", "[%date%][%type%] %message%");
            if( ! is_dir($filePath)) {
                mkdir($filePath);
            }
            self::$_instances[$module] = new \Phalcon\Logger\Adapter\File($filePath."/$module.log");
            self::$_instances[$module]->setLogLevel($level);
            $formatter = new \Phalcon\Logger\Formatter\Line($format, 'Y-m-d H:i:s');
            self::$_instances[$module]->setFormatter( $formatter );
        }
        return self::$_instances[$module];
    }

    public static function getConfig($config, $key, $default)
    {
        $value = $config->$key;
        if($value == null){
            $value = $default;
        }
        return $value;
    }

    public static function writeLog($logType, $str)
    {
        $instance = self::getInstance();
        return $instance->$logType($str);
    }

    /**
     * @param $str
     * @return bool
     */
    public static function error($str)
    {
        return self::writeLog(__FUNCTION__, $str);
    }

    /**
     * @param $str
     * @return bool
     */
    public static function warning($str)
    {
        return self::writeLog(__FUNCTION__, $str);
    }

    /**
     * @param $str
     * @return bool
     */
    public static function info($str)
    {
        return self::writeLog(__FUNCTION__, $str);
    }

    /**
     * @param $str
     * @return bool
     */
    public static function debug($str)
    {
        return self::writeLog(__FUNCTION__, $str);
    }
}