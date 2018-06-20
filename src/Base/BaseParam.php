<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:20
 */

namespace Dai\Framework\Base;

class BaseParam
{
    /**
     * @param $classIns
     * @param $di
     * @param $basePageInfo
     */
    public function vaild(& $classIns, $di, $basePageInfo)
    {
        $request = $di->getRequest();
        $className = get_called_class();
        $reflection = new \ReflectionClass ( $className );

        foreach ( $reflection->getProperties() as $property) {
            $propertyName = $property->name;
            $reflectionProperty = new \ReflectionProperty($className, $propertyName);
            $comment = $reflectionProperty->getDocComment();
            $defaultValue = $reflectionProperty->getValue($classIns);

            $name = \Dai\Framework\Plugin\AnnotationsPlugin::getCommentValue($comment, 'name' );
            $name = $name == "" ? $propertyName : $name;
            $value =  ($basePageInfo->requestType == "post") ? $request->getPost($name) : $request->getQuery($name);

            $type = \Dai\Framework\Plugin\AnnotationsPlugin::getCommentValue($comment, 'type' );
            $length = \Dai\Framework\Plugin\AnnotationsPlugin::getCommentValue($comment, 'length' );
            $regex = \Dai\Framework\Plugin\AnnotationsPlugin::getCommentValue($comment, 'regex' );
            $optional = \Dai\Framework\Plugin\AnnotationsPlugin::getCommentValue($comment, 'optional' );
            $value = $this->getRequestParam($name, $value, $defaultValue, $type, $length, $regex, $optional);
            $classIns->$propertyName = $value;
        }
    }

    /**
     * @param $name
     * @param $value
     * @param $defaultValue
     * @param $type
     * @param $length
     * @param $regex
     * @param $optional
     * @return int|string
     * @throws \Dai\Framework\Base\BaseException
     */
    private function getRequestParam($name, $value, $defaultValue, $type, $length, $regex, $optional)
    {
        // 如果没有传来参数
        if( $value == null ){
            if( $optional == true || $defaultValue != null){
                return $defaultValue;
            }else{
                throw new BaseException( BaseException::PARAM_ERROR, $name);
            }
        }

        if( $type == "Int"  ){
            $value = intval($value);
        }elseif( $type == "String"){
            $value = strval($value);
        }

        //如果正则不匹配
        if( $regex != "") {
            if (! preg_match("/$regex/", $value)) {
                throw new BaseException( BaseException::PARAM_ERROR, "$name,$regex,$value");
            }
        }

        if( $length != ""){
            //如果长度不准确
            $lengthArr = explode(",", $length);
            if( count($lengthArr) == 1 &&  strlen($value) != $lengthArr[0] ) {
                throw new BaseException( BaseException::PARAM_ERROR, $name.",".$lengthArr[0].",".strlen($value));
            }elseif( count($lengthArr) == 2 ){
                if( strlen($value) < $lengthArr[0] || strlen($value)> $lengthArr[1] ){
                    throw new BaseException( BaseException::PARAM_ERROR, $name.",".$lengthArr[0].",".$lengthArr[1].",".strlen($value));
                }
            }
        }
        return $value;
    }
}
