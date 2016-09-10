<?php

namespace PhpAnnotator\Helpers;

/**
 * Config
 *
 */
final class Config
{
    private static $data = [];

    private function __construct()
    {
    }

    public static function write($key, $value)
    {
        self::$data[$key] = $value;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public static function read($key)
    {
        if (!array_key_exists($key, self::$data)) {
            return null;
        }

        return self::$data[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @throws \Exception
     */
    public static function add($key, $value)
    {
        if (array_key_exists($key, self::$data) && !is_array(self::$data[$key])) {
            throw new \Exception('Attempt to add array value to string.');
        }

        self::$data[$key][] = $value;
    }
}
 