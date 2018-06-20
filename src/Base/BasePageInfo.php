<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:20
 */

namespace Dai\Framework\Base;

class BasePageInfo
{
    public $requestType = "get";
    public $login = true;
    public $format = "json";
    public $formCheck = true;
    public $adminLevel = 0;
    public $module;
    public $method;
    public $runMode = "online";

    /** @var  \Dai\Framework\Base\BaseSessionInfo $sessionInfo */
    public $sessionInfo;

    /** @var  \Dai\Framework\Base\BaseParam $params */
    public $params;
}