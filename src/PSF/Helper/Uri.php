<?php

namespace PSF\Helper;


use PSF\Constant;

class Uri
{
    /**
     * 현재 URL path 정보를 배열로 돌려준다
     * @param string $forwardUri
     * @return array
     */
    public static function get($forwardUri = Constant::NONE)
    {
        $uri = $forwardUri? $forwardUri : $_SERVER['REQUEST_URI'];
        $arrTmpUrl = explode('?',$uri);
        if(isset($arrTmpUrl[1])) {
            $params = array();
            parse_str($arrTmpUrl[1], $params);
            $_GET = array_merge($_GET, $params);
        }
        $url = $arrTmpUrl[0];
        $arrUri = explode('/', $url );
        if(preg_match("/^\//i",$uri,$tmpMatch)) array_shift($arrUri);
        return $arrUri;
    }

    /**
     * 도메인을 역으로 정렬해서 돌려준다
     * @param $domain
     * @return string
     */
    public static function reverseDomain($domain)
    {
        return implode('.',array_reverse(explode('.',$domain)));
    }
}