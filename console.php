<?php

include 'autoload.php';

use PhpAnnotator\Helpers\Console;
use PhpAnnotator\Program;

$showHelp = ($argc < 3 || in_array($argv[1], ['help', '-h', 'h']));

if ($showHelp) {
    Console::writeLn('Usage: ./run /method/url input.json [output.yaml]');
    die;
}

if (!file_exists(realpath($argv[2]))) {
    Console::error('Input file does not exist.');
    die;
}

$methodUrl = $argv[1];
$inputFile = $argv[2];
$outputFile = isset($argv[3]) ? $argv[3] : 'output.yaml';

$program = new Program($methodUrl, $inputFile, $outputFile);

$program->runStopwatch();

$program->doIt();

$program->printGoodBye();
