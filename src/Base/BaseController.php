<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/6/20
 * Time: 上午12:07
 */

namespace Dai\Framework\Base;


use Dai\Framework\Library\Trace;

class BaseController extends \Phalcon\Mvc\Controller
{
    public function execute()
    {
        $response = new BaseResponse();

        /** @var \Dai\Framework\Base\BasePageInfo $basePageInfo */
        $basePageInfo = (\Phalcon\DI::getDefault())->get('basePageInfo');
        $module = ucfirst($basePageInfo->module);
        $method = ucfirst($basePageInfo->method);

        $serviceName = "\\". PRJ_NS ."\\Models\\$module\\Service\\$method";

        if (class_exists($serviceName)) {
            try {
                Trace::getInstance()->Add("basePageInfo", $basePageInfo);
                /** @var $serviceIns */
                $serviceIns = new $serviceName();
                $response->data = json_decode(json_encode($serviceIns->execute($basePageInfo)));
            } catch (\Exception $e) {
                $response->code = $e->getCode();
                $response->msg = $e->getMessage();
            }
        }

        Trace::getInstance()->Attach("basePageInfo", []);

        $debugTrace = Trace::getInstance()->getTrace();

        if ($basePageInfo->format == "json" || $debugTrace !== false) {
            if ($debugTrace !== false) {
                $response->debugTrace = $debugTrace;
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $this->view->render(lcfirst($module), lcfirst($method), $response);
        }
    }
}