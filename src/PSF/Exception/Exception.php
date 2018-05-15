<?php

namespace PSF\Exception;


use PSF\Error;
use Throwable;

class Exception extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if($message instanceof Error) {
            $code = $message->getCode();
            $message = $message->getMessage();

        }
        parent::__construct($message, $code, $previous);
    }
}