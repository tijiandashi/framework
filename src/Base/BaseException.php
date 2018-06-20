<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/19
 * Time: 下午3:16
 */

namespace Dai\Framework\Base;

class BaseException extends \Exception
{
    const OK = 0;
    const INTER_ERROR = 1000;
    const PARAM_ERROR = 10001;
    const DB_ERROR = 10002;

    public static $messages = [
        self::OK => "",
        self::INTER_ERROR => '内部错误',
        self::PARAM_ERROR => '参数错误',
        self::DB_ERROR => '数据库操作错误',
    ];

    /**
     * BaseException constructor.
     * @param string $code
     * @param string $param
     */
    public function __construct($code, $param = ""){
        $this->code = $code;
        $this->message = self::getErrorMsg( $code );
        if( $param != ""){
            $this->message .= $param;
        }
    }

    /**
     * @param $errorNo
     * @return mixed
     */
    public static function getErrorMsg($errorNo)
    {
        if (isset(self::$messages[$errorNo])) {
            return self::$messages[$errorNo];
        }else {
            return self::$messages[self::$errorNo];
        }
    }
}