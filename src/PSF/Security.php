<?php

namespace PSF;
use PSF\Exception\SecurityException;

class Security
{
    public static function ruleStart()
    {
        //firewall 가동
        $firewallFlag = Config::site('firewall');
        if (empty($firewallFlag)) $firewallFlag = 0;
        if ($firewallFlag == 1 || strtolower($firewallFlag) == 'on') {
            $allowIPs = Config::site('allowIps');
            if (!in_array($_SERVER['REMOTE_ADDR'], $allowIPs)) {
                throw new SecurityException("Access Denied - " . $_SERVER['REMOTE_ADDR']);
            }
        }
    }
}
