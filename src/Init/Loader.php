<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:15
 */

namespace Dai\Framework\Init;

class Loader
{
    public function init(&$loader, $config)
    {
        /**
         * We're a registering a set of directories taken from the configuration file
         */
        $loader->registerDirs(
            [
                BASE_PATH.$config->application->controllersDir,
                BASE_PATH.$config->application->modelsDir
            ]
        )->register();
    }
}