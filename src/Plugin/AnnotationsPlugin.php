<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:17
 */
namespace Dai\Framework\Plugin;

use Dai\Framework\Base\BasePageInfo;
use Dai\Framework\Library\Runmode;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

class AnnotationsPlugin extends Plugin
{
    /**
     * @param Event $event
     * @param Dispatcher $dispatcher
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        $basePageInfo = new BasePageInfo();
        (\Phalcon\DI::getDefault())->set('basePageInfo', function () use ($basePageInfo) { return $basePageInfo; });
        $basePageInfo->runMode =  Runmode::get();
        $basePageInfo->module = str_replace("Controller", "", $dispatcher->getControllerClass());
        $basePageInfo->method = str_replace("Action", "", $dispatcher->getActiveMethod());
        // 解析目前访问的控制的方法的注释
        $annotations = $this->annotations->getMethod( $dispatcher->getControllerClass(), $dispatcher->getActiveMethod() );

        $basePageInfo->formCheck = $this->getAnnotationValue($annotations, "formCheck");
        if( $this->getAnnotationValue($annotations, "Post") !== null){
            $basePageInfo->requestType = "post";
        }

        if( $this->getAnnotationValue($annotations, "format") !== null){
            $basePageInfo->format = $this->getAnnotationValue($annotations, "format");
        }
        if( $this->getAnnotationValue($annotations, "Login") === false ){
            $basePageInfo->login = false;
        }

        if( trim($this->getAnnotationValue($annotations, "Action")) != ""  ){
            $basePageInfo->method = trim($this->getAnnotationValue($annotations, "Action"));
        }
    }

    /**
     * @param $annotations
     * @param $key
     * @param bool $array
     * @return null
     */
    public function getAnnotationValue($annotations, $key, $array=false)
    {
        $value = null;
        if ($annotations->has($key)) {
            $annotation = $annotations->get($key);
            $values = $annotation->getArguments();

            if( $array === true){
                return $values;
            }
            if( count($values) > 0 ){
                $value = $values[0];
            }
        }
        return $value;
    }
}