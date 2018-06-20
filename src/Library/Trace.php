<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:05
 */

namespace Dai\Framework\Library;

class Trace
{
    private static $_instance = null;
    private static $_docTrace = [];
    public static $debug = false;

    /**
     * Trace constructor.
     */
    public function __construct()
    {
        self::$_docTrace = [];
        self::$debug = false;
    }

    /**
     * @return Trace|null
     */
    public static function getInstance()
    {
        if( self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param $debug
     */
    public function setDebug($debug)
    {
        if($debug == 1 || $debug === "true" || $debug ===true ){
            self::$debug = true;
        }else {
            self::$debug = false;
        }
    }

    /**
     * @param $str
     * @param $paramArr
     * @param $resArr
     * @param int $depth
     * @return array
     */
    private static function _getStr($str, $paramArr=null, $resArr=null, $depth=0)
    {
        $trace = debug_backtrace();
        if( $depth >= count($trace) ) {
            $depth = count($trace) - 1;
        }
        $file =  $trace[$depth]['file'];
        $line = $trace[$depth]['line'];

        $output = [
            'file' => $file.":".$line,
            'msg' => $str,
        ];


        if( !empty($paramArr)){
            if( is_array($paramArr) && isset($paramArr['remoteServer'])) {
                $output ['server'] = $paramArr['remoteServer'];
                unset( $paramArr['remoteServer'] );
            }
            $output ['param'] = $paramArr;
        }

        if( !empty($resArr)){
            $output ['result'] = $resArr;
        }

        return $output;
    }

    /**
     * @param string $str
     * @param array $paramArr
     * @param array $resArr
     * @param int $level
     * @return bool
     */
    public function Add($str="", $paramArr=null, $resArr=null, $level = 1 )
    {
        if( self::getValid() === false) {
            return false;
        }
        $md5 = md5($str);
        $dataArr =  self::_getStr($str, $paramArr, $resArr, $level);
        $dataArr['analysis']['createTime'] = time();
        self::$_docTrace[$md5] = $dataArr;
    }

    /**
     * @param $str
     * @param array $resArr
     * @return bool
     */
    public  function Attach($str, $resArr=null )
    {
        if( self::getValid() === false) {
            return false;
        }
        if( is_string($resArr)) {
            $data = json_decode($resArr, true);
            if( json_last_error() == JSON_ERROR_NONE) {
                $resArr = $data;
            }
        }
        $md5 = md5($str);
        if( $str != "" &&  isset(self::$_docTrace[$md5]) ) {
            self::$_docTrace[$md5]['analysis']['endTime'] = time();
            self::$_docTrace[$md5]['analysis']['timeCost'] = self::$_docTrace[$md5]['analysis']['endTime'] - self::$_docTrace[$md5]['analysis']['createTime'];
            self::$_docTrace[$md5]['analysis']['endTime']  = date('Y-m-d H:i:s', self::$_docTrace[$md5]['analysis']['endTime']);
            self::$_docTrace[$md5]['analysis']['createTime']  = date('Y-m-d H:i:s', self::$_docTrace[$md5]['analysis']['createTime']);
            self::$_docTrace[$md5]['result'] = $resArr;
        }
    }

    /**
     * @return array|bool
     */
    public function getTrace()
    {
        if( self::getValid() === false) {
            return false;
        }
        return array_values(self::$_docTrace);
    }

    /**
     * @return bool
     */
    public static function getValid()
    {
        return self::$debug;
    }
}