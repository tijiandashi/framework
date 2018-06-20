<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/6/19
 * Time: 下午12:00
 */

namespace Dai\Framework\Library;

class CacheClass{

    public static function cacheDataByRedis($method, $tag, \Closure $func, $timeCache = 300){

        $redis = (\Phalcon\DI::getDefault())->get('redis');
        if(empty($method) || empty($tag)){
            return $func();
        }
        $method = str_replace('\\', '_', $method);
        $method = str_replace('::', '@', $method);

        $now = time();
        $timeCache = intval($timeCache) > 0 ? $timeCache : 0;
        $rKey = "BGCache:" . $method . ":" . $tag;
        if($timeCache > 0){
            $json_str = $redis->get($rKey);
            $json = !empty($json_str) ? json_decode($json_str, true) : [];
            $json['_update_'] = isset($json['_update_']) ? $json['_update_'] : 0;
            $data = isset($json['data']) ? $json['data'] : [];
        }

        //缓存时间不允许超过timeCache
        if($timeCache == 0 || !isset($json['data']) || $now - $json['_update_'] > $timeCache){
            $data = $func();
            $timeCache = $timeCache <= 0 ? 300 : $timeCache;
            $redis->set($rKey, json_encode(['data' => $data, '_update_' => $now]), $timeCache);
        }
        return $data;
    }
}