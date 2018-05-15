<?php

namespace PSF\Helper;


use PSF\Constant;

class ArraySearch
{
    public static function searchValueByKey($findKey, $arrayValues = array())
    {
        $findKeyList = explode('.', $findKey);
        $resultValues = $arrayValues;
        foreach($findKeyList as $key){
            if(isset($resultValues[$key])) $resultValues = $resultValues[$key];
            else $resultValues = Constant::NONE;
        }
        return $resultValues;
    }
}