<?php

namespace PSF;


use PSF\Exception\OutputException;

class Output extends Object
{
    /**
     * static 경로에 있는 파일인가
     * @param string $uri
     * @return bool
     * @throws Exception\ConfigException
     */
    protected function isStatic($uri = Constant::NONE)
    {
        if (is_array($uri)) $uri = implode(DIRECTORY_SEPARATOR, $uri);
        $staticDirs = Config::site('dirs.view');
        $staticDirKeys = array_keys($staticDirs);
        if (preg_match('/^(' . implode('|', $staticDirKeys) . '){1}\/(.+)/i', $uri, $tmpMimeMatch)) {
            return true;
        }
        return false;
    }

    protected function display($tpl = Constant::NONE, $properties = array())
    {
        $templateFile = Application::getSiteDir('template') . DIRECTORY_SEPARATOR . $tpl . Constant::DOT . Constant::TEMPLATE_EXTENSION;
        if (is_file($templateFile)) {
            Application::getTemplate()->display($tpl . Constant::DOT . Constant::TEMPLATE_EXTENSION, $properties);
        } else {
            throw new OutputException("Not Found File. {$templateFile}", 404);
        }
    }

    protected function printStatic($staticUri = '')
    {
        if (is_array($staticUri)) $staticUri = implode(DIRECTORY_SEPARATOR, $staticUri);
        $staticDirs = Config::site('dirs.view');
        $staticDirKeys = array_keys($staticDirs);

        $charset = Config::site('charset');
        if (preg_match('/^(' . implode('|', $staticDirKeys) . '){1}\/(.+)/i', $staticUri, $tmpMimeMatch)) {
            $mimeFilePath = '';
            switch ($tmpMimeMatch[1]) {
                case 'image':
                    $mimeFilePath = Application::getSiteDir('view.image') . DIRECTORY_SEPARATOR . $tmpMimeMatch[2];
                    $imageInfo = getimagesize($mimeFilePath);
                    header("Content-type: {$imageInfo['mime']}; charset=" . strtoupper($charset));
                    break;
                case 'js':
                    $mimeFilePath = Application::getSiteDir('view.js') . DIRECTORY_SEPARATOR . $tmpMimeMatch[2];
                    header("Content-Type: application/javascript; charset=" . strtoupper($charset));
                    break;
                case 'css':
                    $mimeFilePath = Application::getSiteDir('view.css') . DIRECTORY_SEPARATOR . $tmpMimeMatch[2];
                    header("Content-type: text/css; charset=" . strtoupper($charset));
                    break;
                case 'font':
                    $mimeFilePath = Application::getSiteDir('view.font') . DIRECTORY_SEPARATOR . $tmpMimeMatch[2];
                    header("Content-type: application/octet-stream; charset=" . strtoupper($charset));
                    break;
                default:
                    $targetFile = Directory::siteRoot() . DIRECTORY_SEPARATOR . $staticUri;
                    $mimeType = mime_content_type($targetFile);
                    header("Content-type: " . $mimeType . "; charset=" . strtoupper($charset));
                    $mimeFilePath = $targetFile;
                    break;
            }
            if (is_file($mimeFilePath)) {
                echo file_get_contents($mimeFilePath);
            } else {
                throw new OutputException("Not Found File. {$mimeFilePath}", 404);
            }
            exit;
        }
    }
}