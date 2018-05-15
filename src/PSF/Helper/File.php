<?php

namespace PSF\Helper;


use PSF\Exception\Exception;

class File
{

    /**
     *
     * @param $fileName
     * @param array $datas
     * @throws Exception
     */
    public static function put_json_pretty($fileName, $datas = array()){
        $filePath = dirname($fileName);
        if(!is_dir($filePath)){
            throw new Exception('The file path not exist.');
        }
        file_put_contents($fileName, json_encode($datas, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
    }
}