<?php

spl_autoload_register(function ($class) {
    $class = str_replace(['\\', 'JsonAnswer2Yaml/'], ['/', ''], $class);

    include __DIR__ . '/src/' . $class . '.php';
});

include 'src/Helpers/functions.php';