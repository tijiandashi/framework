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
    private static $_clients = null;

    private function __construct(){}

    /**
     * @param null $config
     * @return \Redis
     * @throws \Exception
     */
    public static function getInstance($config = null){
        if( $config == null || $config->ip == null || $config->port == null ) {
            $config = ConfigLibrary::get("config", "redis");
        }
        $clientKey = $config->ip."_".$config->port;

        //未被实例化
        if( !isset(self::$_clients[$clientKey]) ){
            try {
                $redisIns = new \Redis();
                $redisIns->connect($config->ip, $config->port);
                self::$_clients[$clientKey] = $redisIns;
                return $redisIns;
            }catch(\Exception $e){
                Log::error("连接redis失败, msg ". $e->getMessage().", code ". $e->getCode());
                throw new \Exception($e->getMessage(),$e->getCode());
            }
        }else {
            return self::$_clients[$clientKey];
        }
    }
}