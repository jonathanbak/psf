<?php
$baseDir = dirname(dirname(__FILE__));
$composerAutoloader = require_once($baseDir . '/vendor/autoload.php');
define('ROOT_DIR', getcwd());
use PSF\Application as App;
use PSF\Installer;

try {
    App::set($composerAutoloader, ROOT_DIR);
    App::install();
    Installer::success();
} catch (\Exception $e) {
    Installer::fail($e->getMessage());
}