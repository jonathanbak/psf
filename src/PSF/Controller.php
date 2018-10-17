<?php

namespace PSF;


abstract class Controller
{

    public function assign($params = array())
    {
        Application::getTemplate()->assign($params);
    }

    public function display($tpl = '', $properties = array())
    {
        if (!$tpl) {
            $callerClass = get_class($this);
            $callerClass = str_replace('\\', Directory::DIRECTORY_SEPARATOR, strtolower(str_replace(Application::site('namespace') . '\\', '', $callerClass)));
            $callerFunc = debug_backtrace()[1]['function'];
            $tplFile = $callerClass . Directory::DIRECTORY_SEPARATOR . strtolower($callerFunc);
        } else {
            $tplFile = $tpl;
        }

        Output::display($tplFile, $properties);
    }

    public function __destruct()
    {
//        ob_end_flush();
    }
}