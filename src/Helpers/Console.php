<?php

namespace JsonAnswer2Yaml\Helpers;

class Console
{
    public static function write($msg)
    {
        if (!Config::read('consoleMute')) {
            echo $msg;
        }
    }

    public static function writeLn($msg = '')
    {
        static::write($msg . PHP_EOL);
    }

    public static function writeProgress($msg)
    {
        static::write("\r");
        static::write($msg);
    }

    public static function successEcho()
    {
        static::writeLn();
        static::writeLn('Execution time: ' . FormatHelper::convertSeconds(microtime(1) - Config::read('startTime')));
        static::writeLn('Max memory usage: ' . FormatHelper::convertBytes(memory_get_peak_usage()));
    }

    public static function error($msg)
    {
        static::writeLn('[Error] ' . $msg);
        die;
    }
}