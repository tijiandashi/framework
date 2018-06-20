<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:19
 */

namespace Dai\Framework\Plugin;


use Dai\Framework\Library\Trace;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

class FormFilterPlugin extends Plugin
{
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        $di = \Phalcon\DI::getDefault();
        /** @var \Dai\Framework\Base\BasePageInfo $basePageInfo */
        $basePageInfo = $di->get('basePageInfo');
        $className = $basePageInfo->formCheck;

        //看看是不是需要debug
        $request = $di->getRequest();
        $debugTpl = $request->getPost("debugTpl");
        if( $debugTpl == null){
            $debugTpl = $request->getQuery("debugTpl");
        }
        Trace::getInstance()->setDebug($debugTpl);

        //如果不需要校验
        if( $className === false){
            return true;
        }

        //如果没有配置，走默认
        if( $className == "" ){
            $module = str_replace( "Controller", "", $basePageInfo->module ) ;
            $action = ucfirst( str_replace( "Action", "", $basePageInfo->method ) );
            $className = "\\". PRG_NS ."\\Models\\$module\\Param\\$action"."Param";
        }

        if( ! class_exists( $className) ) {
            return true;
        }

        /** @var \Dai\Framework\Base\BaseParam $classIns */
        $classIns = new $className();
        $classIns->vaild($classIns, $di, $basePageInfo);
        $basePageInfo->params = $classIns;
    }
}