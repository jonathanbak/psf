<?php

namespace PSF;


class Debug extends Singleton
{
    private $logFileName = "{site}_debug_{date}.log";

    protected function __construct()
    {
        $logDir = Application::getSiteDir('log');
        $this->logFileName = $logDir . DIRECTORY_SEPARATOR . str_replace('{site}',$_SERVER['HTTP_HOST'],str_replace('{date}',date("Ymd"),$this->logFileName) );

        if(is_dir($logDir)==false){
            mkdir($logDir, 0777);
        }
    }

    protected function write( $messages, $loggroup = 'common')
    {
        $baseDir = Directory::siteRoot();
        $backtrace = debug_backtrace();
        $callerFileName = isset($backtrace[0]['file'])? str_replace($baseDir,'',$backtrace[0]['file']) : '';
        if( isset($backtrace[0]['line']) ) $callerFileName .= " (".$backtrace[0]['line'].")";

//        $messages = $self->convertCharset($messages, 'utf8');
        if(is_array($messages)) {
            $messages = json_encode($messages);
            $messages = $this->unicode_decode($messages);
        }

        $messages = "[" .$callerFileName . "] - " . $messages;

        $debugMode = Config::site('debugMode');
        if($debugMode) $this->_log( $messages, $loggroup );

        $displayErrors = Config::site('displayErrors');
        if($displayErrors == 'on' || $displayErrors == '1'){
            if (php_sapi_name() == "cli") echo "(".$loggroup.")".$messages. "\n";
            else echo "<pre style='background-color:black;color:#eee;'>(".$loggroup.")".$messages."<br></pre>";
        }
    }

    protected function _log( $messages,  $loggroup = 'common')
    {
        $messages = "[".date("Y-m-d H:i:s")."] (".$loggroup.") ".$_SERVER['REMOTE_ADDR']." - ". $messages ."\n" ;
        file_put_contents($this->logFileName, $messages, FILE_APPEND);
        chmod($this->logFileName, 0777);
    }

    protected function unicode_decode($str)
    {
        $str = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', create_function ('$match',
            'return mb_convert_encoding(pack("H*", $match[1]), "UTF-8", "UCS-2BE");'
        ), $str);
        return $str;
    }
}