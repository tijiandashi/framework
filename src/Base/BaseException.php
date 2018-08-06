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

    const SMS_SEND_FAILED = 20001;
    const SMS_CHECK_FAILED = 20002;
    const CAN_NOT_APPLY_TEICE = 20003;
    const POS_ORDER_NOT_EXIST = 20004;
    const SHARE_RECORD_ALREADY_EXIST = 20005;
    const POS_ORDER_ALREADY_APPLY = 20006;

    public static $messages = [
        self::OK => "",
        self::INTER_ERROR => '内部错误',
        self::PARAM_ERROR => '参数错误',
        self::DB_ERROR => '数据库操作错误',
        self::SMS_SEND_FAILED => "验证码发送失败",
        self::SMS_CHECK_FAILED => "验证码校验失败",
        self::CAN_NOT_APPLY_TEICE => "无法重复申请",
        self::POS_ORDER_NOT_EXIST => "申请pos机订单不存在",
        self::SHARE_RECORD_ALREADY_EXIST => "该邀请记录已存在",
        self::POS_ORDER_ALREADY_APPLY => "无法重复申请",
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