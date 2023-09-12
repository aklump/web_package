<?php

// TODO Echo eval code

use AKlump\WebPackage\Config\LoadConfig;

require __DIR__ . '/../vendor/autoload.php';

$template = isset($argv[1]) ? $argv[1] : NULL;
$config = (new LoadConfig())($template);

// Convert array to BASH eval code.
$eval = [];
foreach ($config as $key => $value) {
  $eval[] = "wp_$key=\"$value\"";
}

// Create a string of eval code for BASH in the original format.
echo implode(PHP_EOL, $eval) . PHP_EOL;
exit(0);
