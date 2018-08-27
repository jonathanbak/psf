<?php

namespace PSF;

use PSF\Exception\DirectoryException;

class Directory extends Singleton
{
    protected $rootDir;
    protected $appDir;
    protected $configDir;

    protected $siteRootDir;

    protected function root()
    {
        return $this->rootDir ? $this->rootDir : '.';
    }

    protected function setRoot($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    protected function app()
    {
        if($this->appDir==Constant::NONE) throw new DirectoryException();
        $dir = $this->root() . DIRECTORY_SEPARATOR . $this->appDir;
        return $dir;
    }

    protected function setApp($appDir)
    {
        $this->appDir = $appDir;
    }

    protected function config($type = '')
    {
        if($this->configDir==Constant::NONE) throw new DirectoryException();
        $dir = $this->root() . DIRECTORY_SEPARATOR . $this->configDir;
        return $dir . ($type ? DIRECTORY_SEPARATOR . $type : Constant::NONE);
    }

    protected function setConfig($configDir)
    {
        $this->configDir = $configDir;
    }

    protected function siteRoot()
    {
        if($this->siteRootDir==Constant::NONE) throw new DirectoryException();
        return $this->siteRootDir;
    }

    protected function setSiteRoot($siteRootDir)
    {
        if (preg_match('/^[~]/i', $siteRootDir, $tmpMatch)) $dir = str_replace('~', $this->app(), $siteRootDir);
        else $dir = $this->app() . $siteRootDir;

        $this->siteRootDir = $dir;
    }

//    public function sitePath($dir)
//    {
//        $siteConfig = Configure::site('dirs');
//        if (preg_match('/[.]/i', $dir, $tmpMatch)) {
//            $dirs = explode('.', $dir);
//            if (empty($siteConfig[$dirs[0]][$dirs[1]])) throw new DirectoryException("site " . $dir . " 디렉토리 설정이 잘못되었습니다.");
//            $dir = $this->siteRoot() . DIRECTORY_SEPARATOR . $siteConfig[$dirs[0]][$dirs[1]];
//        } else {
//            if (empty($siteConfig[$dir])) throw new DirectoryException("site " . $dir . " 디렉토리 설정이 잘못되었습니다.");
//            $dir = $this->siteRoot() . DIRECTORY_SEPARATOR . $siteConfig[$dir];
//        }
//
//        return $dir;
//    }
}
