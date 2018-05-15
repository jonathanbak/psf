<?php

namespace PSF;

use PSF\Exception\Exception;
use PSF\Helper\Uri;

class Installer
{
    public static function baseDirInfo($domain){
        $reverseDomain = Uri::reverseDomain($domain);
        $baseDirInfo = array(
            "root" => "~/". $reverseDomain,
            "controller" => "controllers",
            "model" => "models",
            "view" => array(
                "image" => "views/images",
                "js" => "views/js",
                "css" => "views/css",
                "font" => "views/fonts"
            ),
            "template" => "views/tpl",
            "temp" => "_tmp",
            "log" => "_tmp/logs",
            "compile" => "_tmp/compile"
        );

        return $baseDirInfo;
    }

    public static function baseRouteInfo()
    {
        return array("autoload"=> "main/main",
            "index"=> "main");
    }

    public static function baseDbInfo()
    {
        return array(
            "driver" => "mysqli",
            "host" => "localhost",
            "username" => "user",
            "password" => "password",
            "database" => "dbname",
            "port" => "3306",
            "charset" => "utf8"
        );
    }

    public static function success()
    {
        echo "OK.\n";
    }

    public static function fail( $errMessage = '' )
    {
        echo $errMessage."\n";
        echo "FAIL.\n";
    }
}