<?php

if (!function_exists('isAssoc')) {
    function isAssoc($arr)
    {
        return $arr && array_keys($arr) !== range(0, count($arr) - 1);
    }
}