<?php

if (!function_exists('isAssoc')) {
    function isAssoc($arr)
    {
        return $arr && array_keys($arr) !== range(0, count($arr) - 1);
    }
}

if (!function_exists('complexUcwords')) {
    function complexUcwords($str)
    {
        return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $str)));
    }
}

