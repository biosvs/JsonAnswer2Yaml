<?php

namespace JsonAnswer2Yaml\Helpers;

class FormatHelper {
    public static function convertBytes($number)
    {
        $len = strlen((string)$number);

        if ($len < 4) {
            return sprintf('%d bytes', $number);
        }

        if ($len >= 4 && $len <= 6) {
            return sprintf('%0.2f Kb', $number / 1024);
        }

        if ($len >= 7 && $len <= 9) {
            return sprintf('%0.2f Mb', $number / 1024 / 1024);
        }

        return sprintf('%0.2f Gb', $number / 1024 / 1024 / 1024);
    }

    public static function convertSeconds($number)
    {
        if ($number < 60) {
            return sprintf('%0.4f sec', $number);
        } else {
            return ($number / 60 % 60) . ' min ' . ($number % 60) . ' sec';
        }
    }

    public static function numberFormat($number)
    {
        return number_format($number, 0, '.', ' ');
    }
}