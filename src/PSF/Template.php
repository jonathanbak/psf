<?php

namespace PSF;


class Template implements TemplateInterface
{
    protected $templatePath;
    protected $cachePath;
    protected $params = array();

    /**
     * 템플릿 위치 지정
     * @param $path
     */
    public function setTemplate($path)
    {
        $this->templatePath = $path;
    }

    /**
     * 템플릿 캐시 저장 위치 지정
     * @param $path
     */
    public function setCached($path)
    {
        $this->cachePath = $path;
    }

    /**
     * 데이터 추가
     * @param array $params
     */
    public function assign($params = array())
    {
        $this->params = $params;
    }

    /**
     * 템플릿 렌더링, 출력
     * @param $templateFile 템플릿 파일명
     * @param array $params 렌더링시 사용할 데이터
     */
    public function display($templateFile, $params = array())
    {
        $params = Helper\ArrayMerge::recursive_distinct($this->params, $params);
        extract($params);
        require($this->templatePath . Directory::DIRECTORY_SEPARATOR . $templateFile);
    }
}