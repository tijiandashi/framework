<?php
/**
 * Created by PhpStorm.
 * User: work
 * Date: 18-6-23
 * Time: 下午4:27
 */

namespace Dai\Framework\Library;

use Dai\Framework\Base\BaseException;
use Dai\Framework\Library\Runmode;

class ConfigLibrary
{
    /**
     * 获取系统配置
     * @param string $sid
     * @param string $module
     * @param string|null $key
     * @return null
     */
    public static function get(string $sid, string $module, string $key=null){
        $ruMode = Runmode::get();
        $configFile = CONFIG_PATH."/$ruMode/$sid.ini";
        $configModule = self::getFromConfigFile( $configFile, $module);
        if( $configModule == null ) {
            $configFile = CONFIG_PATH."/share/$sid.ini";
            $configModule = self::getFromConfigFile( $configFile, $module);
        }

        if( $key == null) {
            return $configModule;
        }else {
            return $configModule->$key;
        }
    }

    /**
     * @param string $configFile
     * @param string $module
     * @return null
     * @throws BaseException
     */
    public static function getFromConfigFile(string $configFile, string $module) {
        if( !file_exists($configFile)) {
            return null;
        }
        try{
            $iniReader = new \Phalcon\Config\Adapter\Ini($configFile);
            if( $iniReader == null || $iniReader->$module == null) {
                return null;
            }else {
                return $iniReader->$module;
            }
        }catch (\Exception $e){
            throw new BaseException(BaseException::INTER_ERROR, $e->getMessage());
        }
    }

    /**
     * @param string $configFile
     * @param string $module
     * @param string $key
     * @return null
     * @throws BaseException
     */
    public static function geFromConfigFileByKey(string $configFile, string $module, string $key){
        if( !file_exists($configFile)) {
            return null;
        }
        try{
            $iniReader = new \Phalcon\Config\Adapter\Ini($configFile);
            if( $iniReader == null || $iniReader->$module == null) {
                return null;
            }else {
                return $iniReader->$module->$key;
            }
        }catch (\Exception $e){
            throw new BaseException(BaseException::INTER_ERROR, $e->getMessage());
        }
    }
}