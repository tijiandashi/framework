<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/6/19
 * Time: 下午12:01
 */

namespace Dai\Framework\Library;


class RedisExt
{
    private static $config;
    private static $_client = Null;

    public function __construct(\Phalcon\Config $config)
    {
        self::$config = $config;
    }

    private static function init(){
        //未被实例化
        if( !isset(self::$_client) ){
            self::$_client = new \Redis();
            try {
                self::$_client->connect(self::$config->ip, self::$config->port);
            }catch(\Exception $e){
                throw new \Exception($e->getMessage(),$e->getCode());
                return false;
            }
        }
    }

    public static function getRedis(){
        $ret = self::init();
        if($ret === false){
            return false;
        }
        return self::$_client;
    }

    public static function set($key, $value, $expire_time = 0){
        $redis_ins = self::getRedis();
        if( $redis_ins == NULL || $redis_ins === false) {
            return false;
        }
        if( $expire_time > 0){
            $ret = $redis_ins->setex($key, $expire_time, $value);
        }else {
            $ret = $redis_ins->set($key, $value);
        }
        if($ret === false){
            return false;
        }
        return true;
    }

    public static function get($key){
        $redis_ins = self::getRedis();
        if( $redis_ins == NULL || $redis_ins === false) {
            return false;
        }
        return $redis_ins->get($key);
    }
}