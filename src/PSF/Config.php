<?php

namespace PSF;

use PSF\Exception\ConfigException;
use PSF\Helper\ArraySearch;

class Config extends Singleton
{
    static $site = array();
    static $db = array();
    protected $directory;
    protected $currentSite;
    protected $commonConfig;

    protected function init()
    {
        $this->cli();
        Directory::setApp($this->common('appDir'));
        Directory::setConfig($this->common('configDir'));
    }

    protected function getCommonConfigFile()
    {
        $configDir = Directory::root();

        return $configDir . Directory::DIRECTORY_SEPARATOR . Constant::COMMON_CONFIG_FILE;
    }

    protected function common($key = '')
    {
        $configDir = Directory::root();

        if (!is_file($configDir . Directory::DIRECTORY_SEPARATOR . Constant::COMMON_CONFIG_FILE)) {
            throw new ConfigException('COMMON ' . new Error(Error::NOT_FOUND_CONFIG) . "(" . Constant::COMMON_CONFIG_FILE . ")");
        }

        $this->commonConfig = empty($this->commonConfig) ? $this->load($configDir . Directory::DIRECTORY_SEPARATOR . Constant::COMMON_CONFIG_FILE) : $this->commonConfig;

        if ($key) {
            $resultValue = ArraySearch::searchValueByKey($key, $this->commonConfig);
            return $resultValue;
        } else {
            return $this->commonConfig;
        }
    }

    protected function cli()
    {
        $backtrace = debug_backtrace();
        $backtrace = array_pop($backtrace);
        if ($this->isCli() == true && $backtrace['function'] != 'spl_autoload_call' && $backtrace['function'] != 'include') {
            if (empty($_SERVER['REMOTE_ADDR'])) {
                $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            }
            $pathInfo = $backtrace['class'] == "PHPUnit_TextUI_Command" ? parse_url($_SERVER['argv'][2]) : parse_url($_SERVER['argv'][1]);
            if (empty($pathInfo['host']) || empty($pathInfo['path'])) {
                throw new ConfigException(new Error(Error::INVALID_COMMAND));
            }
            if (preg_match('/([^:]+):([0-9]+)$/i', $pathInfo['host'], $tmpMatch)) {
                $_SERVER['SERVER_NAME'] = $tmpMatch[1];
                $_SERVER['SERVER_PORT'] = $tmpMatch[2];
            } else {
                $_SERVER['SERVER_NAME'] = $pathInfo['host'];
                $_SERVER['SERVER_PORT'] = 80;
            }
            array_shift($_SERVER['argv']);
            array_shift($_SERVER['argv']);
            $_SERVER['argc']--;
            $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT'] != '80' ? ':' . $_SERVER['SERVER_PORT'] : '');
            $_SERVER['REQUEST_URI'] = $pathInfo['path'];
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }
    }

    protected function isCli()
    {
        return php_sapi_name() == 'cli' ? true : false;
    }

    protected function load($configFile)
    {
        if (!is_file($configFile)) {
            throw new ConfigException(new Error(Error::NOT_FOUND_CONFIG) . "(" . $configFile . ")");
        }
        $configure = file_get_contents($configFile);
        return json_decode($configure, true);
    }


    protected function site($key = '', $currentSite = '')
    {
        $configDir = Directory::config(Constant::DIR_CONFIG_SITE);

        if(!$currentSite) $currentSite = $this->getCurrentSite();

        $siteConfigFile = $currentSite . Constant::DOT . Constant::CONFIG_EXTENSION;
        if (!is_file($configDir . Directory::DIRECTORY_SEPARATOR . $siteConfigFile)) {
            throw new ConfigException($currentSite . new Error(Error::NOT_FOUND_CONFIG) . "(" . $siteConfigFile . ")");
        }

        self::$site[$currentSite] = !isset(self::$site[$currentSite]) ? $this->load($configDir . Directory::DIRECTORY_SEPARATOR . $siteConfigFile) : self::$site[$currentSite];
        if ($key) {
            $resultValue = ArraySearch::searchValueByKey($key, self::$site[$currentSite]);
            return $resultValue;
        } else {
            return self::$site[$currentSite];
        }
    }

    protected function db($dbAlias = '', $dbFileName = '')
    {
        if (!$dbFileName) $dbFileName = $this->site('dbset');
        $configDir = Directory::config(Constant::DIR_CONFIG_DB);
        $dbConfigFile = $dbFileName . Constant::DOT . Constant::CONFIG_EXTENSION;

        if (!is_file($configDir . Directory::DIRECTORY_SEPARATOR . $dbConfigFile)) {
            throw new ConfigException($dbFileName . new Error(Error::NOT_FOUND_CONFIG) . "(" . $dbConfigFile . ")");
        }

        self::$db[$dbFileName] = !isset(self::$db[$dbFileName]) ? $this->load($configDir . Directory::DIRECTORY_SEPARATOR . $dbConfigFile) : self::$db[$dbFileName];

        if ($dbAlias) {
            return isset(self::$db[$dbFileName][$dbAlias]) ? self::$db[$dbFileName][$dbAlias] : Constant::NONE;
        } else {
            return self::$db[$dbFileName];
        }
    }

    protected function setCurrentSite($siteUrl)
    {
        $this->currentSite = $siteUrl;
    }

    protected function getCurrentSite()
    {
        return !empty($this->currentSite) ? $this->currentSite : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : Constant::NONE) ;
    }
}