<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:14
 */
namespace Dai\Framework\Init;

use Dai\Framework\Library\ConfigLibrary;
use Dai\Framework\Library\Runmode;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Mvc\Router\Annotations as RouterAnnotations;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Events\Manager as EventsManager;

class Service
{

    public function init(&$di)
    {
        $runMode = Runmode::get();

        /**
         * Setting up the view component
         */
        $di->setShared('view', function () {
            $configApplication = ConfigLibrary::get("config", "application");

            $view = new View();
            $view->setDI($this);
            $view->setViewsDir(BASE_PATH.$configApplication->viewsDir);

            $view->registerEngines([
                '.volt' => function ($view) use ($configApplication) {
                    $cachePath =  BASE_PATH.$configApplication->cacheDir;
                    if( !is_dir($cachePath)) {
                        mkdir($cachePath);
                    }
                    $volt = new VoltEngine($view, $this);
                    $volt->setOptions([
                        'compiledPath' => $cachePath,
                        'compiledSeparator' => '_'
                    ]);
                    return $volt;
                },
                '.phtml' => PhpEngine::class
            ]);

            return $view;
        });

        /**
         * Start the session the first time some component request the session service
         */
        $di->setShared('session', function () {
            $session = new SessionAdapter();
            $session->start();

            return $session;
        });

        $di->set('cookies', function () {
            $cookies = new \Phalcon\Http\Response\Cookies();
            $cookies->useEncryption(false); //默认使用加密方式，但这里我们刚开始使用非加密方式
            return $cookies;
        });

        $di->setShared('router', function () use ($di) {
            $router = new RouterAnnotations(false);
            $routerConfigs =  explode(",", ConfigLibrary::get("config", "router", "list"));
            foreach ($routerConfigs as $module) {
                $router->addResource(ucfirst($module), '/'.lcfirst($module));
            }
            return $router;
        });


        $di->setShared( 'dispatcher', function () use ($di){
            $eventsManager = new EventsManager();
            // 添加插件到dispatch事件中
            $plugins = explode(",", ConfigLibrary::get("config", "plugin", "list"));
            foreach ($plugins as $plugin) {
                $pluginArr = explode("_", $plugin);
                if( count($pluginArr) == 2 && $pluginArr[0] == "SYS") {
                    $pluginName = "\\Dai\\Framework\\Plugin\\".$pluginArr[1];
                }else {
                    $pluginName = "\\".PRJ_NS."\\Plugin\\".$plugin;
                }

                if( class_exists($pluginName)) {
                    $eventsManager->attach('dispatch', new $pluginName());
                }
            }
            $dispatcher = new MvcDispatcher();
            $dispatcher->setEventsManager($eventsManager);
            return $dispatcher;
        });


        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        $di->setShared('db', function () use ($di) {
            $configDb = ConfigLibrary::get("config", "database");
            $class = 'Phalcon\Db\Adapter\Pdo\\' . $configDb->adapter;
            $params = [
                'host'     => $configDb->host,
                'username' => $configDb->username,
                'password' => $configDb->password,
                'dbname'   => $configDb->dbname,
                'charset'  => $configDb->charset,
                'port' => isset($configDb->port) ? $configDb->port : 3306,
            ];
            return new $class($params);
        });
    }
}