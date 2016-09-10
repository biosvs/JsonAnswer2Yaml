<?php

namespace JsonAnswer2Yaml;

use JsonAnswer2Yaml\Helpers\Traits\YamlFormatterTrait;

class Model
{
    use YamlFormatterTrait;

    const RESPONSE_CODE_OFFSET = 0;
    const DEFINITIONS_OFFSET = 0;

    protected $name = '';

    protected $result = '';

    /** @var Model[] */
    protected $outlineModels = [];

    public function __construct($name, $rawObject, $lvl)
    {
        $this->name = $name;
        $this->result = $this->parseObject($rawObject, $lvl);
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOutlineModels()
    {
        return $this->outlineModels;
    }

    public function getResult()
    {
        return $this->result;
    }

    protected function parseObject($obj, $lvl)
    {
        $result = '';
        $lvlUp = $lvl + 1;
        $fields = [];

        foreach ($obj as $parameterName => $parameterValue) {
            if (!is_array($parameterValue)) {
                $fields[] = $this->wrapNumber($parameterName);

                $result .= $this->getLineString($lvl, $parameterName);
                $result .= $this->getLineString($lvlUp, 'description');
                $result .= $this->getScalarValueScheme($lvlUp, $parameterValue);
            } elseif (isAssoc($parameterValue)) {
                $fields[] = $this->wrapNumber($parameterName);

                $result .= $this->getLineString($lvl, $parameterName);
                $result .= $this->getLineString($lvlUp, 'type', 'object');
                $result .= $this->getLineString($lvlUp, 'description');

                list($parseResult, $requiredFields) = $this->parseObject($parameterValue, $lvlUp + 1);

                $result .= $this->getLineStringRequired($lvlUp, $requiredFields);
                $result .= $this->getLineString($lvlUp, 'properties');
                $result .= $parseResult;
            } else {
                $result .= $this->getLineString($lvl, $parameterName);
                $result .= $this->getLineString($lvlUp, 'type', 'array');
                $result .= $this->getLineString($lvlUp, 'description');
                $result .= $this->getLineString($lvlUp, 'items');

                $result .= $this->parseArrayElement($parameterName, $parameterValue[0], $lvlUp + 1);
            }
        }

        return [$result, $fields];
    }

    protected function parseArrayElement($parameterName, $elementValue, $lvl)
    {
        $result = '';

        if (is_array($elementValue)) {
            $result .= $this->getLineStringRef($lvl + 1, $parameterName);

            $model = new Model($parameterName, $elementValue, static::DEFINITIONS_OFFSET + 3);

            $this->outlineModels[] = $model;
            $this->outlineModels = array_merge($this->outlineModels, $model->getOutlineModels());
        } else {
            $result .= $this->getScalarValueScheme($lvl, $elementValue);
        }

        return $result;
    }

    protected function getScalarValueScheme($lvl, $value)
    {
        $type = '';
        $format = '';

        switch(true) {
            case is_int($value):
                $type = 'integer';
                $format = 'int64';
                break;

            case is_float($value):
                $type = 'number';
                $format = 'float';
                break;

            case is_bool($value):
                $type = 'boolean';
                break;

            case is_string($value):
                $type = 'string';
                break;

            default:
                $type = 'undefined';
        }

        $result = $this->getLineString($lvl, 'type', $type);

        if ($format !== '') {
            $result .= $this->getLineString($lvl, 'format', $format);
        }

        return $result;
    }
}