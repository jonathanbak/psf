<?php

namespace PSF;


use PSF\Exception\OutputException;

class Output extends Singleton
{
    /**
     * static 경로에 있는 파일인가
     * @param string $uri
     * @return bool
     * @throws Exception\ConfigException
     */
    protected function isStatic($uri = Constant::NONE)
    {
        if (is_array($uri)) $uri = implode(Directory::DIRECTORY_SEPARATOR, $uri);
        $staticDirs = Config::site('dirs.view');
        $staticDirKeys = array_keys($staticDirs);
        if (preg_match('/^(' . implode('|', $staticDirKeys) . '){1}\/(.+)/i', $uri, $tmpMimeMatch)) {
            return true;
        }
        return false;
    }

    protected function display($tpl = Constant::NONE, $properties = array())
    {
        $templateExtension = Constant::DOT . (Config::site("extensionTemplate") ? Config::site("extensionTemplate") : Constant::TEMPLATE_EXTENSION);
        $templateFile = Application::getSiteDir('template') . Directory::DIRECTORY_SEPARATOR . $tpl . $templateExtension;
        if (is_file($templateFile)) {
            Application::getTemplate()->display($tpl . $templateExtension, $properties);
        } else {
            throw new OutputException("Not Found File. {$templateFile}", 404);
        }
    }

    protected function printStatic($staticUri = '')
    {
        if (is_array($staticUri)) $staticUri = implode(Directory::DIRECTORY_SEPARATOR, $staticUri);
        $staticDirs = Config::site('dirs.view');
        $staticDirKeys = array_keys($staticDirs);

        $charset = Config::site('charset');
        if (preg_match('/^(' . implode('|', $staticDirKeys) . '){1}\/(.+)/i', $staticUri, $tmpMimeMatch)) {
            $mimeFilePath = '';
            switch ($tmpMimeMatch[1]) {
                case 'image':
                    $mimeFilePath = Application::getSiteDir('view.image') . Directory::DIRECTORY_SEPARATOR . $tmpMimeMatch[2];
                    if (!is_file($mimeFilePath)) throw new OutputException("Not Found File. {$mimeFilePath}", 404);
                    $imageInfo = getimagesize($mimeFilePath);
                    header("Content-type: {$imageInfo['mime']}; charset=" . strtoupper($charset));
                    break;
                case 'js':
                    $mimeFilePath = Application::getSiteDir('view.js') . Directory::DIRECTORY_SEPARATOR . $tmpMimeMatch[2];
                    header("Content-Type: application/javascript; charset=" . strtoupper($charset));
                    break;
                case 'css':
                    $mimeFilePath = Application::getSiteDir('view.css') . Directory::DIRECTORY_SEPARATOR . $tmpMimeMatch[2];
                    header("Content-type: text/css; charset=" . strtoupper($charset));
                    break;
                case 'font':
                    $mimeFilePath = Application::getSiteDir('view.font') . Directory::DIRECTORY_SEPARATOR . $tmpMimeMatch[2];
                    header("Content-type: application/octet-stream; charset=" . strtoupper($charset));
                    break;
                default:
                    $targetFile = Directory::siteRoot() . Directory::DIRECTORY_SEPARATOR . $staticUri;
                    if (!is_file($targetFile)) throw new OutputException("Not Found File. {$targetFile}", 404);
                    $mimeType = mime_content_type($targetFile);
                    header("Content-type: " . $mimeType . "; charset=" . strtoupper($charset));
                    $mimeFilePath = $targetFile;
                    break;
            }
            if (is_file($mimeFilePath)) {
                $this->cacheHeader($mimeFilePath);
                echo file_get_contents($mimeFilePath);
            } else {
                throw new OutputException("Not Found File. {$mimeFilePath}", 404);
            }
            exit;
        }
    }

    protected function cacheHeader($file)
    {
        $lastModified = filemtime($file);
        $etagFile = md5_file($file);

        $ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
        $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

        //set last-modified header
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
        //set etag-header
        header("Etag: $etagFile");
        //header('Cache-Control: public');

        //check if page has changed. If not, send 304 and exit
        if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified || $etagHeader == $etagFile) {
            header("HTTP/1.1 304 Not Modified");
            exit;
        }

    }
}