<?php

namespace PSF;


interface TemplateInterface
{
    /**
     * 템플릿 위치 지정
     * @param $path
     */
    public function setTemplate($path);

    /**
     * 템플릿 캐시 저장 위치 지정
     * @param $path
     */
    public function setCached($path);

    /**
     * 데이터 추가
     * @param array $params
     */
    public function assign($params = array());

    /**
     * 템플릿 렌더링, 출력
     * @param $templateFile 템플릿 파일명
     * @param array $params 렌더링시 사용할 데이터
     */
    public function display($templateFile, $params = array());
}