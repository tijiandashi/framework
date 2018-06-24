<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/6/24
 * Time: 下午1:57
 */

namespace Dai\Framework\Library;


class Sms
{
    public static function sendSms($config, $mobile,$code){
        $tkey      = date('YmdHis',time());
        $username  = $config['username'];
        $password  = $config['password'];
        $pwd       = md5(md5($password).$tkey);
        $uri       = $config['uri'];
        $content   = $config['content'];
        $content   = str_replace('{code}',$code,$content);
        $productid = $config['productid'];

        $data['username']  = $username;
        $data['password']  = $pwd;
        $data['tkey']      = $tkey;
        $data['mobile']    = $mobile;
        $data['content']   = $content;
        $data['productid'] = $productid;
        $result = HttpProxy::call($uri, $data);
        Log::debug(sprintf("%s %s, data[%s], result [%s]",
            __CLASS__, __FUNCTION__, var_export($data, true), var_export($result, true) ));
        return $result;
    }
}