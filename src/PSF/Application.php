<?php

namespace PSF;

use \Composer\Autoload\ClassLoader;
use PSF\Exception\ConfigException;
use PSF\Exception\DirectoryException;
use PSF\Exception\Exception;
use PSF\Helper\ArraySearch;
use PSF\Helper\ArrayMerge;
use PSF\Helper\File;
use PSF\Helper\Uri;

class Application extends Singleton
{
    protected $template;
    protected $autoloader;

    protected function set(ClassLoader $autoLoader, $rootDir = '')
    {
        if ($autoLoader instanceof ClassLoader === false) throw new Exception(new Error(Error::REQUIRE_AUTOLOADER));
        $this->autoloader = $autoLoader;
        Directory::setRoot(empty($rootDir) ? dirname($_SERVER['DOCUMENT_ROOT']) : $rootDir);
        Config::init();
        if (Config::common('installed')) {
            $this->setSiteRoot();
            $this->autoload();
        }
    }

    protected function reset(ClassLoader $autoLoader, $rootDir = '')
    {
        if ($autoLoader instanceof ClassLoader === false) throw new Exception(new Error(Error::REQUIRE_AUTOLOADER));
        $this->autoloader = $autoLoader;
        Directory::setRoot(empty($rootDir) ? dirname($_SERVER['DOCUMENT_ROOT']) : $rootDir);
        Config::init();

        $newConfigure = ArrayMerge::recursive_distinct(Config::common(), array('installed' => '0'));
        $configFileName = Config::getCommonConfigFile();
        File::put_json_pretty($configFileName, $newConfigure);
    }

    protected function setTemplate(TemplateInterface $template)
    {
        $this->template = $template;
    }

    protected function getTemplate()
    {
        return $this->template;
    }

    /**
     * autoload 등록
     */
    protected function autoload()
    {
        $siteNamespace = Config::site('namespace');
        $this->autoloader->setPsr4($siteNamespace . "\\", array($this->getSiteDir('controller')));
        $this->autoloader->setPsr4($siteNamespace . "\\Model\\", array($this->getSiteDir('model')));
    }

    /**
     * set application directory
     * @param $rootDir
     */
    protected function setRoot($rootDir)
    {
        Directory::setRoot($rootDir);
    }

    protected function getSiteDir($dir)
    {
        $siteConfig = Config::site('dirs');
        $resultPath = ArraySearch::searchValueByKey($dir, $siteConfig);
        if ($resultPath === Constant::NONE) throw new DirectoryException("site " . $dir . " 디렉토리 설정이 잘못되었습니다.");
        $dir = Directory::siteRoot() . Directory::DIRECTORY_SEPARATOR . $resultPath;
        return $dir;
    }


    protected function start(ClassLoader $autoLoader = NULL, $rootDir = '')
    {
        if ($autoLoader !== NULL) $this->set($autoLoader, $rootDir);

        Config::cli();
        //site settting
        $this->setSite();

        $currentUri = Uri::get();
        if (Output::isStatic($currentUri)) {
            //static 이면 바로 처리
            Output::printStatic($currentUri);
        } else {
            Router::execute($currentUri);
        }
    }

    protected function setSiteRoot()
    {
        $dir = 'root';
        $siteConfig = Config::site('dirs');
        $siteRootDir = ArraySearch::searchValueByKey($dir, $siteConfig);
        if ($siteRootDir === Constant::NONE) throw new DirectoryException("site " . $dir . " 디렉토리 설정이 잘못되었습니다.");

        Directory::setSiteRoot($siteRootDir);
    }

    protected function setSite()
    {
        //setting template
        $templateDir = $this->getSiteDir('template');
        $cacheDir = $this->getSiteDir('compile');

        if (!$this->template) {
            $this->template = new Template();
        }
        $this->template->setTemplate($templateDir);
        $this->template->setCached($cacheDir);
    }

    protected function setCommonDir()
    {
        /**
         * create basic directory
         */
        $appRoot = Directory::app();
        if (!is_dir($appRoot)) mkdir($appRoot, 0755, true);
        $path = Directory::root() . Directory::DIRECTORY_SEPARATOR . Config::common('configDir');
        if (!is_dir($path)) mkdir($path, 0755, true);
        $path = Directory::config(Constant::DIR_CONFIG_SITE);
        if (!is_dir($path)) mkdir($path, 0755, true);
        $path = Directory::config(Constant::DIR_CONFIG_DB);
        if (!is_dir($path)) mkdir($path, 0755, true);

    }

    protected function install()
    {
        if (!Config::common('installed')) {

            $this->setCommonDir();
            $newConfigure = ArrayMerge::recursive_distinct(Config::common(), array('installed' => '1'));

            $this->createDb(false);

            $this->createSite(false);

            $configFileName = Config::getCommonConfigFile();
            File::put_json_pretty($configFileName, $newConfigure);

        } else {
            echo "Already installed.\n";
        }
    }

    protected function createSite($standalone = true)
    {
        $domainName = '';
        $namespace = '';
        $redirectSite = '';
        $dbSet = '';
        $inputDatas = array();

        if ($standalone) {
            $this->setCommonDir();
            $params = $_SERVER['argv'];
            array_shift($params);
            $inputParams = array_chunk($params, 2);
            foreach ($inputParams as $input) {
                $option = isset($input[0]) ? $input[0] : '';
                $value = isset($input[1]) ? $input[1] : '';
                switch ($option) {
                    case '-h':  //도메인
                        $domainName = $value;
                        break;
                    case '-ns': //네임스페이스
                        $namespace = $value;
                        break;
                    case '-r': //연결 도메인
                        $redirectSite = $value;
                        break;
                    case '-db': //디비연결
                        $dbSet = $value;
                        break;
                    case '-d': //추가 파라미터 설정
                        $inputDatas = json_decode($value, true);
                        break;
                    default:
                        echo "Unknown option - " . $option . "\n";
                        break;
                }
            }
        }

        if (!$namespace) {
            echo "Create site configuration file [N/y]?";
            fscanf(STDIN, "%s\n", $createYesNo); // reads number from STDIN
            if (strtolower($createYesNo) != 'y') {
                return false;
            }
        }
        if (!$namespace) {
            echo "Input site namespace : ";
            fscanf(STDIN, "%s\n", $namespace); // reads number from STDIN
            echo $namespace . "\n";
        }
        if (!$domainName) {
            echo "Input site domain : ";
            fscanf(STDIN, "%s\n", $domainName); // reads number from STDIN
            echo $domainName . "\n";
        }
        if (!$redirectSite) $redirectSite = $domainName;

        if (!$dbSet) {
            echo "Input db file name : ";
            fscanf(STDIN, "%s\n", $dbSet); // reads number from STDIN
            echo $dbSet . "\n";
        }
        if (!$dbSet) {
            $dbSet = $domainName;
        }

        $rootDir = Directory::root();
        $command = "grep '\"namespace\": \"" . $namespace . "\"' " . $rootDir . "/config/site/* | awk '{print $1}' | sed 's/" . str_replace('/', '\/', $rootDir) . "\/config\/site\/\(.*\):/\\1/g'";
        echo $command . "\n";
        @exec($command, $result);
        $defaultConfigure = array(
            "host" => $domainName,
            "description" => "",
            "charset" => "utf-8",
            "development" => "1",
            "displayErrors" => "1",
            "debugMode" => "1",
            "firewall" => "0",
            "allowIps" => array(
                "127.0.0.1"
            ),
            "extension" => "php",
            "namespace" => $namespace,
            "include_sites" => array(),
            "dirs" => Installer::baseDirInfo($domainName),
            "extensionTemplate" => "tpl",
            "dbset" => $dbSet,
            "route" => Installer::baseRouteInfo()
        );
        try {
            $aleadyConfigure = null;
            if (isset($result[0])) {
                $aleadySiteInfo = pathinfo($result[0]);
                $aleadySite = $aleadySiteInfo['filename'];
                Config::setCurrentSite($aleadySite);
                $aleadyConfigure = Config::site();
//    $configFilePath = \SMB\Directory::config_site().'/'.$result[0];
            }

            Config::setCurrentSite($domainName);
            $siteConfigure = Config::site();
        } catch (ConfigException $e) {
            //없으면 신규 생성. 있으면 수정
//    echo $e->getMessage();
            echo "Create new site..\n";
            $siteConfigure = $defaultConfigure;
            Config::setCurrentSite($domainName);
        }
        if ($aleadyConfigure !== null) {
            $siteConfigure = ArrayMerge::recursive_distinct($aleadyConfigure, $siteConfigure);
        }
        $siteNewConfigure = ArrayMerge::recursive_distinct($siteConfigure, $inputDatas);
//        Configure::setSiteConfigure($redirectSite, $siteNewConfigure);

//dbset 검증
        if (!empty($siteNewConfigure['dbset'])) {
            try {
                $dbInfo = Config::db('', $siteNewConfigure['dbset']);
            } catch (ConfigException $e) {
                echo $e->getMessage() . "\n";
                exit;
            }
        }

        $configFileName = Directory::config(Constant::DIR_CONFIG_SITE) . Directory::DIRECTORY_SEPARATOR . $redirectSite . Constant::DOT . Constant::CONFIG_EXTENSION;
        File::put_json_pretty($configFileName, $siteNewConfigure);
        try {
            //폴더 생성
            $this->setSiteRoot();
            $this->setSite();
            $siteRoot = Directory::siteRoot();
            if (!is_dir($siteRoot)) mkdir($siteRoot, 0755, true);
            $path = $this->getSiteDir('controller');
            if (!is_dir($path)) mkdir($path, 0755, true);
            $path = $this->getSiteDir('model');
            if (!is_dir($path)) mkdir($path, 0755, true);
            $path = $this->getSiteDir('view.image');
            if (!is_dir($path)) mkdir($path, 0755, true);
            $path = $this->getSiteDir('view.js');
            if (!is_dir($path)) mkdir($path, 0755, true);
            $path = $this->getSiteDir('view.css');
            if (!is_dir($path)) mkdir($path, 0755, true);
            $path = $this->getSiteDir('template');
            if (!is_dir($path)) mkdir($path, 0755, true);
            $path = $this->getSiteDir('temp');
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
                chmod($path, 0777);
            }
            $path = $this->getSiteDir('log');
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
                chmod($path, 0777);
            }
            $path = $this->getSiteDir('compile');
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
                chmod($path, 0777);
            }

        } catch (DirectoryException $e) {
            Installer::fail($e->getMessage());
            exit;
        }

        $newConfigure = ArrayMerge::recursive_distinct(Config::common(), array('installed' => '1'));
        $configFileName = Config::getCommonConfigFile();
        File::put_json_pretty($configFileName, $newConfigure);
    }

    protected function createDb($standalone = true)
    {
        $host = '';
        $user = '';
        $password = '';
        $dbName = '';
        $dbAlias = '';
        $fileName = '';
        $inputDatas = array();

        if ($standalone) {
            $this->setCommonDir();
            $params = $_SERVER['argv'];
            array_shift($params);
            $inputParams = array_chunk($params, 2);
            foreach ($inputParams as $input) {
                $option = isset($input[0]) ? $input[0] : '';
                $value = isset($input[1]) ? $input[1] : '';
                switch ($option) {
                    case '-u':  //user
                        $user = $value;
                        break;
                    case '-p': //password
                        $password = $value;
                        break;
                    case '-h': //host
                        $host = $value;
                        break;
                    case '-db': //db name
                        $dbName = $value;
                        break;
                    case '-as': //db alias
                        $dbAlias = $value;
                        break;
                    case '-f': //config file name
                        $fileName = $value;
                        break;
                    case '-d': //추가 파라미터 설정
                        $inputDatas = json_decode($value, true);
                        break;
                    default:
                        echo "Unknown option - " . $option . "\n";
                        break;
                }
            }
        }

        if (!$host) {
            echo "Create database configuration file [N/y]?";
            fscanf(STDIN, "%s\n", $createDBYesNo); // reads number from STDIN
            if (strtolower($createDBYesNo) != 'y') {
                return false;
            }
        }

        if (!$fileName) {
            echo "Input db file name (domain name) : ";
            fscanf(STDIN, "%s\n", $fileName); // reads number from STDIN
            echo $fileName . "\n";
        }
        if (!$host) {
            echo "Input db host : ";
            fscanf(STDIN, "%s\n", $host); // reads number from STDIN
            echo $host . "\n";
        }
        if (!$user) {
            echo "Input db user : ";
            fscanf(STDIN, "%s\n", $user); // reads number from STDIN
            echo $user . "\n";
        }
        if (!$password) {
            echo "Input db password : ";
            fscanf(STDIN, "%s\n", $password); // reads number from STDIN
            echo $password . "\n";
        }
        if (!$dbName) {
            echo "Input database name : ";
            fscanf(STDIN, "%s\n", $dbName); // reads number from STDIN
            echo $dbName . "\n";
        }
        if (!$dbAlias) {
            echo "Input db alias name : ";
            fscanf(STDIN, "%s\n", $dbAlias); // reads number from STDIN
            echo $dbAlias . "\n";
        }
        if (!$dbAlias) $dbAlias = $dbName;
        if (!$fileName) {
            $fileName = $host;
        }
        $dbConfigure = array();
        try {
            $dbConfigure = Config::db('', $fileName);
            if (isset($dbConfigure[$dbAlias])) {
                $dbConfigure[$dbAlias] = array_merge($dbConfigure[$dbAlias], array("host" => $host,
                    "username" => $user,
                    "password" => $password,
                    "database" => $dbName));
            } else {
                $dbConfigure[$dbAlias] = array_merge(Installer::baseDbInfo(), array("host" => $host,
                    "username" => $user,
                    "password" => $password,
                    "database" => $dbName));
            }
        } catch (ConfigException $e) {
            //없으면 신규 생성. 있으면 수정
//    echo $e->getMessage();
            echo "Create new db..\n";
            $mainDbConfigure = array_merge(Installer::baseDbInfo(), array("host" => $host,
                "username" => $user,
                "password" => $password,
                "database" => $dbName));

            $dbConfigure[$dbAlias] = $mainDbConfigure;
        }

        $dbConfigure[$dbAlias] = ArrayMerge::recursive_distinct($dbConfigure[$dbAlias], $inputDatas);

        $configFileName = Directory::config(Constant::DIR_CONFIG_DB) . Directory::DIRECTORY_SEPARATOR . $fileName . Constant::DOT . Constant::CONFIG_EXTENSION;
        echo $configFileName . "\n";
        File::put_json_pretty($configFileName, $dbConfigure);
    }
}