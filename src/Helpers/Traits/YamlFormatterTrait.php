<?php

namespace PhpAnnotator\Helpers\Traits;

trait YamlFormatterTrait {
    protected function getLineString($lvl, $name, $value = '')
    {
        if ($name === 'description' && $value === '') {
            $value = 'fillme';
        }

        return $this->getSpaces($lvl)
        . $this->wrapNumber($name)
        . ': '
        . $value
        . PHP_EOL;
    }

    protected function getLineStringRequired($lvl, $fields)
    {
        if (!$fields) {
            return '';
        }

        $fieldsListString = implode(', ', $fields);

        return $this->getLineString($lvl, 'required', '['. $fieldsListString .']');
    }

    protected function getLineStringRef($lvl, $modelName)
    {
        return $this->getLineString($lvl, '$ref', sprintf("'#/definitions/%s'", $modelName));
    }

    protected function wrapNumber($value)
    {
        return is_numeric($value) ? sprintf('"%s"', $value) : $value;
    }

    protected function getSpaces($lvl)
    {
        return str_repeat('  ', $lvl);
    }
}