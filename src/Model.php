<?php

namespace JsonAnswer2Yaml;

use JsonAnswer2Yaml\Helpers\Console;
use JsonAnswer2Yaml\Helpers\Traits\YamlFormatterTrait;

class Model
{
    use YamlFormatterTrait;

    /** Offset in tabs (by 2 spaces) */
    const RESPONSE_CODE_OFFSET = 0;
    const DEFINITIONS_OFFSET = 0;

    /** @var string Model name */
    protected $name = '';

    /** @var array Result in yaml format and used fields */
    protected $result = [];

    /** @var Model[] */
    protected $outlineModels = [];

    /**
     * @param string $name Model name
     * @param array $rawObject Parsed array from json
     * @param int $lvl Offset in tabs
     */
    public function __construct($name, $rawObject, $lvl)
    {
        $this->name = $name;
        $this->result = $this->parseObject($rawObject, $lvl);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return nested models
     *
     * @return Model[]
     */
    public function getOutlineModels()
    {
        return $this->outlineModels;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array $obj Array for parse
     * @param int $lvl Offset in tabs
     * @return array Result in yaml format and used fields
     */
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

                $firstValue = isset($parameterValue[0]) ? $parameterValue[0] : null;

                $result .= $this->parseArrayElement($parameterName, $firstValue, $lvlUp + 1);
            }
        }

        return [$result, $fields];
    }

    /**
     * @param string $parameterName
     * @param string|array $elementValue
     * @param int $lvl Offset in tabs
     * @return string
     */
    protected function parseArrayElement($parameterName, $elementValue, $lvl)
    {
        $result = '';

        if (is_array($elementValue)) {
            $result .= $this->getLineStringRef($lvl + 1, $parameterName);

            $modelName = complexUcwords($parameterName);

            Console::writeLn('I found the new model. Is it good name? (Write new name or just press enter.)');
            $modelName = Console::prompt(sprintf('Model "%s": ', $modelName), $modelName);

            $model = new Model($modelName, $elementValue, static::DEFINITIONS_OFFSET + 3);

            $this->outlineModels[] = $model;
            $this->outlineModels = array_merge($this->outlineModels, $model->getOutlineModels());
        } else {
            $result .= $this->getScalarValueScheme($lvl, $elementValue);
        }

        return $result;
    }

    /**
     * @param int $lvl Offset in tabs
     * @param mixed $value
     * @return string
     */
    protected function getScalarValueScheme($lvl, $value)
    {
        $type = '';
        $format = '';
        $nullable = false;

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

            case is_null($value):
                $type = 'null';
                $nullable = true;
                break;

            default:
                $type = 'undefined';
        }

        $result = $this->getLineString($lvl, 'type', $type);

        if ($format !== '') {
            $result .= $this->getLineString($lvl, 'format', $format);
        }

        if ($nullable) {
            $result .= $this->getLineString($lvl, 'x-nullable', 'true');
        }

        return $result;
    }
}