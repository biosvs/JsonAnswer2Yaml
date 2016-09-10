<?php

namespace PhpAnnotator;

use PhpAnnotator\Helpers\Config;
use PhpAnnotator\Helpers\Console;
use PhpAnnotator\Helpers\Traits\YamlFormatterTrait;

class Program
{
    use YamlFormatterTrait;

    protected $methodUrl;

    protected $inputFile;

    protected $outputFile;

    /** @var Model[] */
    protected $outlineModels = [];

    public function __construct($methodUrl, $inputFile, $outputFile)
    {
        $this->methodUrl = str_replace('/', '.', trim($methodUrl, '/'));
        $this->inputFile = $inputFile;
        $this->outputFile = $outputFile;
    }

    public function doIt()
    {
        $arr = $this->getParsedArray();

        $result  = $this->getLineString(Model::RESPONSE_CODE_OFFSET, '200');
        $result .= $this->getLineString(Model::RESPONSE_CODE_OFFSET + 1, 'description');
        $result .= $this->getLineString(Model::RESPONSE_CODE_OFFSET + 1, 'scheme');

        $modelName = 'Response-' . $this->methodUrl;
        $mainModel = new Model($modelName, $arr, Model::DEFINITIONS_OFFSET + 3);

        $result .= $this->getLineStringRef(Model::RESPONSE_CODE_OFFSET + 2, $modelName);

        $outlinesModels = array_merge([$mainModel], $mainModel->getOutlineModels());
        $result .= $this->createDefinitions($outlinesModels);

        file_put_contents(__DIR__ . '/../' . $this->outputFile, $result);
    }

    protected function createDefinitions(array $outlineModels)
    {
        $result  = PHP_EOL . PHP_EOL;
        $result .= $this->getLineString(Model::DEFINITIONS_OFFSET, 'definitions');

        /** @var Model $model */
        foreach ($outlineModels as $model) {
            $result .= $this->getLineString(Model::DEFINITIONS_OFFSET + 1, $model->getName());
            $result .= $this->getLineString(Model::DEFINITIONS_OFFSET + 2, 'type', 'object');

            list($parseResult, $requiredFields) = $model->getResult();

            $result .= $this->getLineStringRequired(Model::DEFINITIONS_OFFSET + 2, $requiredFields);
            $result .= $this->getLineString(Model::DEFINITIONS_OFFSET + 2, 'properties');
            $result .= $parseResult;
        }

        return $result;
    }

    protected function getParsedArray()
    {
        $content = file_get_contents($this->inputFile);
        $array = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Console::error('Json parsing failed (is it correct json?).');
        }

        if (empty($array)) {
            Console::error('Json parsed, but array is empty.');
        }

        if (!isAssoc($array)) {
            Console::error('Answer cannot be a numeric array.');
        }

        return $array;
    }

    public function runStopwatch()
    {
        Config::write('startTime', microtime(1));
    }

    public function printGoodBye()
    {
        Console::successEcho();
    }
}