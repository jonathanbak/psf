<?php
define('ROOT_DIR', getcwd());
$composerAutoloader = require_once(ROOT_DIR . '/vendor/autoload.php');
use PSF\Application as App;
use PSF\Installer;

try {
    App::reset($composerAutoloader, ROOT_DIR);
    App::createSite();
    Installer::success();
} catch (\Exception $e) {
    Installer::fail($e->getMessage());
}