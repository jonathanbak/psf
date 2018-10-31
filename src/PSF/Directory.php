<?php

namespace PSF;

use PSF\Exception\DirectoryException;
use PSF\Helper\ArraySearch;

class Directory extends Singleton
{
    const DIRECTORY_SEPARATOR = '/';

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
        $dir = $this->root() . Directory::DIRECTORY_SEPARATOR . $this->appDir;
        return $dir;
    }

    protected function setApp($appDir)
    {
        $this->appDir = $appDir;
    }

    protected function config($type = '')
    {
        if($this->configDir==Constant::NONE) throw new DirectoryException();
        $dir = $this->root() . Directory::DIRECTORY_SEPARATOR . $this->configDir;
        return $dir . ($type ? Directory::DIRECTORY_SEPARATOR . $type : Constant::NONE);
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
        $this->siteRootDir = $this->getSiteRoot($siteRootDir);
    }

    protected function getSiteRoot($siteRootDir)
    {
        if (preg_match('/^[~]/i', $siteRootDir, $tmpMatch)) $dir = str_replace('~', $this->app(), $siteRootDir);
        else $dir = $this->app() . $siteRootDir;

        return $dir;
    }

    protected function getSiteDir($dir, $siteUrl = '')
    {
        $siteConfig = Config::site('dirs', $siteUrl);
        $siteRootDir = ArraySearch::searchValueByKey('root', $siteConfig);
        if ($siteRootDir === Constant::NONE) throw new DirectoryException("site root 디렉토리 설정이 잘못되었습니다.");
        $siteRootDir = $this->getSiteRoot($siteRootDir);

        $resultPath = ArraySearch::searchValueByKey($dir, $siteConfig);
        if ($resultPath === Constant::NONE) throw new DirectoryException("site " . $dir . " 디렉토리 설정이 잘못되었습니다.");
        $dir = $siteRootDir . Directory::DIRECTORY_SEPARATOR . $resultPath;
        return $dir;
    }

}
