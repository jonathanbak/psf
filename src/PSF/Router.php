<?php

namespace PSF;

use PSF\Exception\RouterException;
use PSF\Helper\Uri;

class Router extends Object
{
    /**
     * User Defined Route Information
     * @var array
     */
    protected $routes = array();

    /**
     * GET 방식 라우팅 정보 입력
     * @param $uri
     * @param $action
     */
    protected function get($uri, $action)
    {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * POST 방식 라우팅 정보 입력
     * @param $uri
     * @param $action
     */
    protected function post($uri, $action)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * GET,POST등 모든 방식 라우팅 정보 입력
     * @param $uri
     * @param $action
     */
    protected function any($uri, $action)
    {
        $verbs = array('GET', 'HEAD', 'POST');
        return $this->addRoute($verbs, $uri, $action);
    }

    /**
     * 라우팅 정보 추가
     * @param $method
     * @param $uri
     * @param $action
     */
    protected function addRoute($method, $uri, $action)
    {
        $this->routes[] = array($method, $uri, $action);
    }

    /**
     * 라우팅 실행 - URI 와 클래스를 매칭하여 실행한다
     * @param $currentUri
     * @return mixed|void
     * @throws Exception\ConfigException
     * @throws RouterException
     */
    protected function execute($currentUri)
    {
        //현재 URL 에 맞는 route 실행
        $routeConfig = Config::site('route');

        if (count($this->routes) === 0) {

        } else {
            foreach ($this->routes as $key => $route) {
                list($method, $uri, $action) = $route;
                if (!is_array($method)) $method = array($method);
                if (preg_match('/^\//i', $uri, $tmpMatch)) {
                    $uri = substr($uri, 1);
                }
                if ($uri == implode('/', $currentUri) && in_array(strtoupper($_SERVER['REQUEST_METHOD']), $method)) {
                    if (is_object($action)) {
                        $currentUri = $action;
                    } else {
                        $currentUri = Uri::get($action);
                    }
                }
            }
        }

        if (is_object($currentUri)) {
            return call_user_func_array($currentUri, array());
        } else {
            if (count($currentUri) == 1 && empty($currentUri[0])) {
                $currentUri = Uri::get($routeConfig['autoload']);
            }
            return $this->callClassByUri($currentUri);
        }
    }

    /**
     * URI 와 Class 매칭
     * @param $currentUri
     * @throws Exception\ConfigException
     * @throws RouterException
     */
    protected function callClassByUri($currentUri)
    {
        $siteNamespace = Config::site('namespace');
        $loadClassName = $siteNamespace . '\\' . implode('\\', $this->ucFirstArray($currentUri));
        if (class_exists($loadClassName)) {
            $callClass = new $loadClassName();
            $methodList = get_class_methods($callClass);
            $START_METHOD = 'main';
            if (!in_array($START_METHOD, $methodList)) {
                throw new RouterException("Not Found File - " . $loadClassName, 404);
            }
            call_user_func_array(array($callClass, $START_METHOD), array());
        } else {
            $method = array_pop($currentUri);
            $loadClassName = $siteNamespace . '\\' . implode('\\', $this->ucFirstArray($currentUri));
            if (!class_exists($loadClassName)) {
                throw new RouterException("Not Found Class File - " . $loadClassName, 404);
            }
            $callClass = new $loadClassName();
            $methodList = get_class_methods($callClass);
            if (!in_array($method, $methodList)) throw new RouterException("Not Found File - " . $loadClassName, 404);
            call_user_func_array(array($callClass, $method), array());
        }
    }

    /**
     * 배열 값을 각각 ucfirst 하여 돌려줌
     * @param $values
     * @return array
     */
    protected function ucFirstArray($values)
    {
        $response = array();
        foreach ($values as $val) {
            $response[] = ucfirst($val);
        }
        return $response;
    }
}