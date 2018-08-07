<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/6/24
 * Time: 下午1:57
 */

namespace Dai\Framework\Library;


class HttpProxy
{
    //参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
    public static function call($url, $post = null, $headers =null, $timeout = 10, $refer=null, $cookiePath = null, $withCookie = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);

        if( $headers) {
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
        }

        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }
        if ($cookiePath) {
            curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiePath);
            curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiePath);
        }
        if($refer) {
            curl_setopt($curl, CURLOPT_REFERER, $refer);
        }

        if( trim($withCookie) != "") {
            curl_setopt($curl, CURLOPT_COOKIEJAR, $withCookie);//用来存放登录成功的cookie
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        return $data;
    }
}